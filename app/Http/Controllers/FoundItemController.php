<?php

namespace App\Http\Controllers;

use App\Enums\ItemStatus;
use App\Enums\ItemType;
use App\Http\Requests\OwnedItemIndexRequest;
use App\Http\Requests\StoreItemReportRequest;
use App\Http\Requests\UpdateItemReportRequest;
use App\Http\Requests\UpdateItemStatusRequest;
use App\Models\Item;
use App\Services\ItemService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FoundItemController extends Controller
{
    public function __construct(private readonly ItemService $items) {}

    /**
     * List the authenticated user's own found reports.
     */
    public function index(OwnedItemIndexRequest $request): Response
    {
        $filters = $request->filters();

        return Inertia::render('items/found/Index', [
            'items' => $this->items->paginateOwnedReports($request->user(), ItemType::Found, $filters),
            ...$this->items->filterOptions(),
            'filters' => $filters,
            'statusOptions' => ItemStatus::options(),
        ]);
    }

    /**
     * Show the create form.
     */
    public function create(): Response
    {
        return Inertia::render('items/found/Create', [
            ...$this->items->filterOptions(),
        ]);
    }

    /**
     * Store a new found report for the authenticated user.
     */
    public function store(StoreItemReportRequest $request): RedirectResponse
    {
        $this->items->createReport(
            $request->user(),
            ItemType::Found,
            $request->reportData(),
            $request->file('image'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Found report created.')]);

        return to_route('found-items.index');
    }

    /**
     * Show the edit form for an owned found report.
     */
    public function edit(Request $request, Item $found_item): Response
    {
        Gate::authorize('updateFound', $found_item);

        $found_item->load(['category:id,name', 'location:id,name']);

        return Inertia::render('items/found/Edit', [
            'item' => [
                'id' => $found_item->id,
                'title' => $found_item->title,
                'description' => $found_item->description,
                'category_id' => $found_item->category_id,
                'location_id' => $found_item->location_id,
                'event_date' => $found_item->event_date?->toDateString(),
                'image_path' => $found_item->image_path,
                'status' => $found_item->status->value,
                'type' => $found_item->type->value,
            ],
            ...$this->items->filterOptions(),
        ]);
    }

    /**
     * Update an owned found report.
     */
    public function update(UpdateItemReportRequest $request, Item $found_item): RedirectResponse
    {
        Gate::authorize('updateFound', $found_item);

        $this->items->updateReport(
            $found_item,
            $request->reportData(),
            $request->file('image'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Found report updated.')]);

        return to_route('found-items.index');
    }

    /**
     * Change an owned found report's status.
     */
    public function updateStatus(UpdateItemStatusRequest $request, Item $found_item): RedirectResponse
    {
        Gate::authorize('updateFoundStatus', $found_item);

        $this->items->updateReportStatus($found_item, $request->status());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Report status updated.')]);

        return back();
    }

    /**
     * Permanently delete an owned found report.
     */
    public function destroy(Item $found_item): RedirectResponse
    {
        Gate::authorize('deleteFound', $found_item);

        $this->items->deleteReport($found_item);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Found report deleted.')]);

        return to_route('found-items.index');
    }
}
