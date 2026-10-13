<?php

namespace App\Services;

use App\Enums\ItemType;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ItemService
{
    /**
     * Default number of reports returned per page.
     */
    public const int PER_PAGE = 12;

    /**
     * Build the public, paginated list of item reports.
     *
     * @param  array{search?: string|null, type?: string|null, category?: int|null, location?: int|null}  $filters
     * @return LengthAwarePaginator<int, Item>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Item::query()
            ->with(['category:id,name', 'location:id,name'])
            ->when($this->searchTerm($filters), function (Builder $query, string $term): void {
                $query->where(function (Builder $query) use ($term): void {
                    $query->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->when($this->type($filters), function (Builder $query, ItemType $type): void {
                $query->where('type', $type);
            })
            ->when($filters['category'] ?? null, function (Builder $query, int $categoryId): void {
                $query->where('category_id', $categoryId);
            })
            ->when($filters['location'] ?? null, function (Builder $query, int $locationId): void {
                $query->where('location_id', $locationId);
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    /**
     * Selectable filter options for the listing page.
     *
     * @return array{categories: array<int, array{id: int, name: string}>, locations: array<int, array{id: int, name: string}>}
     */
    public function filterOptions(): array
    {
        return [
            'categories' => Category::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                ])
                ->all(),
            'locations' => Location::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Location $location): array => [
                    'id' => $location->id,
                    'name' => $location->name,
                ])
                ->all(),
        ];
    }

    /**
     * @param  array{search?: string|null, type?: string|null, category?: int|null, location?: int|null}  $filters
     */
    private function searchTerm(array $filters): ?string
    {
        $term = trim($filters['search'] ?? '');

        return $term === '' ? null : $term;
    }

    /**
     * @param  array{search?: string|null, type?: string|null, category?: int|null, location?: int|null}  $filters
     */
    private function type(array $filters): ?ItemType
    {
        $type = $filters['type'] ?? null;

        return $type === null ? null : ItemType::tryFrom($type);
    }
}
