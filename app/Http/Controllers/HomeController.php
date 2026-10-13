<?php

namespace App\Http\Controllers;

use App\Enums\ItemStatus;
use App\Enums\ItemType;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Number of recent reports shown on the landing page.
     */
    private const int RECENT_REPORTS_LIMIT = 12;

    /**
     * Render the public landing page with the newest lost and found reports.
     *
     * The page is public, so the payload is limited to data that is safe to
     * expose to guests: no owner identity and no contact details.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Welcome', [
            'reports' => $this->recentReports(),
            'categories' => $this->categories(),
            'statusOptions' => ItemStatus::options(),
            'typeOptions' => ItemType::options(),
        ]);
    }

    /**
     * The newest reports across both types, capped for the landing page.
     *
     * @return array<int, array<string, mixed>>
     */
    private function recentReports(): array
    {
        return Item::query()
            ->with(['category:id,name', 'location:id,name'])
            ->latest('created_at')
            ->latest('id')
            ->limit(self::RECENT_REPORTS_LIMIT)
            ->get([
                'id',
                'category_id',
                'location_id',
                'title',
                'type',
                'description',
                'image_path',
                'status',
                'event_date',
            ])
            ->map(fn (Item $item): array => [
                'id' => $item->id,
                'title' => $item->title,
                'type' => $item->type->value,
                'description' => $item->description,
                'image_path' => $item->image_path,
                'status' => $item->status->value,
                'event_date' => $item->event_date?->toDateString(),
                'category' => [
                    'id' => $item->category->id,
                    'name' => $item->category->name,
                ],
                'location' => [
                    'id' => $item->location->id,
                    'name' => $item->location->name,
                ],
            ])
            ->all();
    }

    /**
     * Selectable category filters, taken from the database.
     *
     * @return array<int, array{id: int, name: string}>
     */
    private function categories(): array
    {
        return Category::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->all();
    }
}
