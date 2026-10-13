<?php

namespace App\Http\Controllers;

use App\Enums\ItemStatus;
use App\Enums\ItemType;
use App\Http\Requests\StoreClaimRequest;
use App\Http\Requests\StoreFoundNotificationRequest;
use App\Models\Item;
use App\Services\ClaimService;
use App\Services\FoundNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ItemDetailController extends Controller
{
    public function __construct(
        private readonly ClaimService $claims,
        private readonly FoundNotificationService $foundNotifications,
    ) {}

    /**
     * Show a report's detail page, shaped by the viewer's relationship to it.
     *
     * Only publicly shareable report fields are always sent. Owner-only panels
     * (claims, found notifications) and any claimant contact details are added
     * solely for the authenticated owner, so private data never leaves the
     * server for anyone else.
     */
    public function show(Request $request, Item $item): Response
    {
        Gate::authorize('view', $item);

        $item->load(['category:id,name', 'location:id,name']);

        $user = $request->user();
        $isOwner = $user !== null && $user->id === $item->user_id;

        return Inertia::render('items/Show', [
            'item' => [
                'id' => $item->id,
                'title' => $item->title,
                'type' => $item->type->value,
                'description' => $item->description,
                'image_path' => $item->image_path,
                'status' => $item->status->value,
                'event_date' => $item->event_date?->toDateString(),
                'created_at' => $item->created_at?->toDateString(),
                'category' => [
                    'id' => $item->category->id,
                    'name' => $item->category->name,
                ],
                'location' => [
                    'id' => $item->location->id,
                    'name' => $item->location->name,
                ],
            ],
            'statusOptions' => ItemStatus::options(),
            'permissions' => $this->permissionsFor($item, $user, $isOwner),
            // Owner-only panels. Absent (null) for everyone else.
            'claims' => $isOwner ? $this->claimsForOwner($item) : null,
            'foundNotifications' => $isOwner ? $this->notificationsForOwner($item) : null,
            'ownerLinks' => $isOwner ? $this->ownerLinks($item) : null,
        ]);
    }

    /**
     * Report a lost item as found. Never changes the lost report itself.
     */
    public function storeFoundNotification(StoreFoundNotificationRequest $request, Item $item): RedirectResponse
    {
        $this->foundNotifications->submit(
            $item,
            $request->user(),
            $request->notificationData(),
            $request->file('image'),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Your message has been sent to the reporter.'),
        ]);

        return back();
    }

    /**
     * Submit an ownership claim for a found report.
     */
    public function storeClaim(StoreClaimRequest $request, Item $item): RedirectResponse
    {
        $this->claims->submit(
            $item,
            $request->user(),
            $request->claimData(),
            $request->file('proof_image'),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Your claim has been submitted for review.'),
        ]);

        return back();
    }

    /**
     * Action flags for the current viewer, computed on the server so Vue only
     * renders what the backend already allows.
     *
     * @return array{
     *     isOwner: bool,
     *     canManage: bool,
     *     canReportFound: bool,
     *     canClaim: bool,
     *     canEdit: bool,
     *     canDelete: bool
     * }
     */
    private function permissionsFor(Item $item, mixed $user, bool $isOwner): array
    {
        return [
            'isOwner' => $isOwner,
            'canManage' => $isOwner,
            'canReportFound' => $user !== null
                && ! $isOwner
                && $this->foundNotifications->acceptsNotifications($item),
            'canClaim' => $user !== null
                && ! $isOwner
                && $this->claims->acceptsClaims($item),
            'canEdit' => $isOwner,
            'canDelete' => $isOwner,
        ];
    }

    /**
     * Owner-only claim list, including claimant contact details needed for the
     * return process.
     *
     * @return array<int, array<string, mixed>>
     */
    private function claimsForOwner(Item $item): array
    {
        return $this->claims->forOwner($item)
            ->map(fn ($claim): array => [
                'id' => $claim->id,
                'proof_description' => $claim->proof_description,
                'proof_image' => $claim->proof_image,
                'status' => $claim->status->value,
                'rejection_reason' => $claim->rejection_reason,
                'created_at' => $claim->created_at?->toDateString(),
                'claimant' => [
                    'name' => $claim->claimant->name,
                    'email' => $claim->claimant->email,
                    'phone_number' => $claim->claimant->phone_number,
                ],
            ])
            ->all();
    }

    /**
     * Owner-only found-notification list for a lost report.
     *
     * @return array<int, array<string, mixed>>
     */
    private function notificationsForOwner(Item $item): array
    {
        if ($item->type !== ItemType::Lost) {
            return [];
        }

        return $this->foundNotifications->forOwner($item)
            ->map(fn ($notification): array => [
                'id' => $notification->id,
                'note' => $notification->note,
                'image_path' => $notification->image_path,
                'created_at' => $notification->created_at?->toDateString(),
                'reporter' => [
                    'name' => $notification->reporter->name,
                    'email' => $notification->reporter->email,
                    'phone_number' => $notification->reporter->phone_number,
                ],
            ])
            ->all();
    }

    /**
     * Owner management links, so the page reuses the existing CRUD routes.
     *
     * @return array{edit: string, delete: string, index: string}
     */
    private function ownerLinks(Item $item): array
    {
        $prefix = $item->type === ItemType::Found ? 'found-items' : 'lost-items';

        return [
            'edit' => route($prefix.'.edit', $item),
            'delete' => route($prefix.'.destroy', $item),
            'index' => route($prefix.'.index'),
        ];
    }
}
