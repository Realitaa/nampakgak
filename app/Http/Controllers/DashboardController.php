<?php

namespace App\Http\Controllers;

use App\Enums\ClaimStatus;
use App\Enums\ItemStatus;
use App\Enums\ItemType;
use App\Models\Claim;
use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the authenticated user's dashboard overview.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('DashboardUser', [
            'summary' => $this->summary($user->id),
            'reports' => $this->reports($user->id),
            'claims' => $this->claims($user->id),
            'statusOptions' => ItemStatus::options(),
            'claimStatusOptions' => ClaimStatus::options(),
        ]);
    }

    /**
     * Aggregate counts for the signed-in user's own activity.
     *
     * @return array{activeLost: int, activeFound: int, pendingClaims: int, resolved: int}
     */
    private function summary(int $userId): array
    {
        $ownedItemCount = fn (Builder $query): int => (int) $query
            ->where('user_id', $userId)
            ->count();

        $activeLost = $ownedItemCount(
            Item::query()->where('type', ItemType::Lost)->where('status', ItemStatus::Active),
        );

        $activeFound = $ownedItemCount(
            Item::query()->where('type', ItemType::Found)->where('status', ItemStatus::Active),
        );

        $resolved = $ownedItemCount(
            Item::query()->where('status', ItemStatus::Resolved),
        );

        $pendingClaims = (int) Claim::query()
            ->where('status', ClaimStatus::Pending)
            ->whereHas('item', fn (Builder $query) => $query->where('user_id', $userId))
            ->count();

        return [
            'activeLost' => $activeLost,
            'activeFound' => $activeFound,
            'pendingClaims' => $pendingClaims,
            'resolved' => $resolved,
        ];
    }

    /**
     * The user's most recent reports, both lost and found.
     *
     * @return array<int, array<string, mixed>>
     */
    private function reports(int $userId): array
    {
        return Item::query()
            ->where('user_id', $userId)
            ->with(['category:id,name', 'location:id,name'])
            ->latest('created_at')
            ->limit(5)
            ->get([
                'id',
                'category_id',
                'location_id',
                'title',
                'type',
                'status',
                'event_date',
                'created_at',
            ])
            ->map(fn (Item $item): array => [
                'id' => $item->id,
                'title' => $item->title,
                'type' => $item->type->value,
                'status' => $item->status->value,
                'category' => [
                    'id' => $item->category->id,
                    'name' => $item->category->name,
                ],
                'location' => [
                    'id' => $item->location->id,
                    'name' => $item->location->name,
                ],
                'event_date' => $item->event_date?->toDateString(),
                'created_at' => $item->created_at?->toDateTimeString(),
            ])
            ->all();
    }

    /**
     * Claims awaiting the user's review and claims the user submitted.
     *
     * @return array<int, array<string, mixed>>
     */
    private function claims(int $userId): array
    {
        $incoming = Claim::query()
            ->whereHas('item', fn (Builder $query) => $query->where('user_id', $userId))
            ->with([
                'item:id,user_id,title',
                'claimant:id,name',
            ])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Claim $claim): array => [
                'id' => $claim->id,
                'direction' => 'incoming',
                'reportTitle' => $claim->item->title,
                'reportId' => $claim->item_id,
                'claimant' => $claim->claimant->name,
                'submittedAt' => $claim->created_at?->toDateString() ?? '',
                'status' => $claim->status->value,
                'proofDescription' => $claim->proof_description,
                'rejectionReason' => $claim->rejection_reason,
            ])
            ->all();

        $submitted = Claim::query()
            ->where('claimant_id', $userId)
            ->with('item:id,user_id,title')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Claim $claim): array => [
                'id' => $claim->id,
                'direction' => 'submitted',
                'reportTitle' => $claim->item->title,
                'reportId' => $claim->item_id,
                'claimant' => null,
                'submittedAt' => $claim->created_at?->toDateString() ?? '',
                'status' => $claim->status->value,
                'proofDescription' => $claim->proof_description,
                'rejectionReason' => $claim->rejection_reason,
            ])
            ->all();

        return array_merge($incoming, $submitted);
    }
}
