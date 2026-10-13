<?php

namespace App\Http\Controllers;

use App\Enums\ItemType;
use App\Http\Requests\ItemIndexRequest;
use App\Services\ItemService;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function __construct(private readonly ItemService $items) {}

    /**
     * Browse the public list of lost and found reports.
     */
    public function index(ItemIndexRequest $request): Response
    {
        $filters = $request->filters();

        return Inertia::render('items/Index', [
            'items' => $this->items->paginate($filters),
            ...$this->items->filterOptions(),
            'filters' => [
                'search' => $filters['search'],
                'type' => $filters['type'],
                'category' => $filters['category'],
                'location' => $filters['location'],
            ],
            'typeOptions' => ItemType::options(),
        ]);
    }
}
