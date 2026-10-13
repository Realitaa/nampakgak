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

class LostItemController extends Controller
{
    public function __construct(private readonly ItemService $items) {}

    /**
     * List the authenticated user's own lost reports.
     */
    public function index(OwnedItemIndexRequest $request): Response
    {
        $filters = $request->filters();

        return Inertia::render('items/lost/Index', [
            'items' => $this->items->paginateOwnedReports($request->user(), ItemType::Lost, $filters),
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
        return Inertia::render('items/lost/Create', [
            ...$this->items->filterOptions(),
        ]);
    }

    /**
     * Store a new lost report for the authenticated user.
     */
    public function store(StoreItemReportRequest $request): RedirectResponse
    {
        $this->items->createReport(
            $request->user(),
            ItemType::Lost,
            $request->reportData(),
            $request->file('image'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Lost report created.')]);

        return to_route('lost-items.index');
    }

    /**
     * Show the edit form for an owned lost report.
     */
    public function edit(Request $request, Item $lost_item): Response
    {
        Gate::authorize('updateLost', $lost_item);

        $lost_item->load(['category:id,name', 'location:id,name']);

        return Inertia::render('items/lost/Edit', [
            'item' => [
                'id' => $lost_item->id,
                'title' => $lost_item->title,
                'description' => $lost_item->description,
                'category_id' => $lost_item->category_id,
                'location_id' => $lost_item->location_id,
                'event_date' => $lost_item->event_date?->toDateString(),
                'image_path' => $lost_item->image_path,
                'status' => $lost_item->status->value,
                'type' => $lost_item->type->value,
            ],
            ...$this->items->filterOptions(),
        ]);
    }

    /**
     * Update an owned lost report.
     */
    public function update(UpdateItemReportRequest $request, Item $lost_item): RedirectResponse
    {
        Gate::authorize('updateLost', $lost_item);

        $this->items->updateReport(
            $lost_item,
            $request->reportData(),
            $request->file('image'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Lost report updated.')]);

        return to_route('lost-items.index');
    }

    /**
     * Change an owned lost report's status.
     */
    public function updateStatus(UpdateItemStatusRequest $request, Item $lost_item): RedirectResponse
    {
        Gate::authorize('updateLostStatus', $lost_item);

        $this->items->updateReportStatus($lost_item, $request->status());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Report status updated.')]);

        return back();
    }

    /**
     * Permanently delete an owned lost report.
     */
    public function destroy(Item $lost_item): RedirectResponse
    {
        Gate::authorize('deleteLost', $lost_item);

        $this->items->deleteReport($lost_item);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Lost report deleted.')]);

        return to_route('lost-items.index');
    }
}
