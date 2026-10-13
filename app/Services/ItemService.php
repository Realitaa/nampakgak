<?php

namespace App\Services;

use App\Enums\ItemStatus;
use App\Enums\ItemType;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ItemService
{
    /**
     * Default number of reports returned per page.
     */
    public const int PER_PAGE = 12;

    /**
     * Disk used for item report images.
     */
    public const string IMAGE_DISK = 'public';

    /**
     * Directory on the image disk where report photos are stored.
     */
    public const string IMAGE_DIRECTORY = 'items';

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
     * Paginated reports of the given type owned by the user, with search and
     * filters. Ownership and the report type are enforced by the query, not by
     * the caller, so neither can be bypassed.
     *
     * @param  array{search?: string|null, status?: string|null, category?: int|null, location?: int|null}  $filters
     * @return LengthAwarePaginator<int, Item>
     */
    public function paginateOwnedReports(User $user, ItemType $type, array $filters): LengthAwarePaginator
    {
        return Item::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->with(['category:id,name', 'location:id,name'])
            ->when($this->searchTerm($filters), function (Builder $query, string $term): void {
                $query->where(function (Builder $query) use ($term): void {
                    $query->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->when($this->status($filters), function (Builder $query, ItemStatus $status): void {
                $query->where('status', $status);
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
     * Create a report of the given type for the user. Ownership, type, and the
     * initial status are assigned here and never read from the request.
     *
     * @param  array{title: string, description: string, category_id: int, location_id: int, event_date: string|null}  $data
     */
    public function createReport(User $user, ItemType $type, array $data, ?UploadedFile $image): Item
    {
        return DB::transaction(function () use ($user, $type, $data, $image): Item {
            $storedPath = $image?->store(self::IMAGE_DIRECTORY, self::IMAGE_DISK);
            $imagePath = $storedPath === false ? null : $storedPath;

            return Item::query()->create([
                ...$data,
                'user_id' => $user->id,
                'type' => $type,
                'status' => ItemStatus::Active,
                'image_path' => $imagePath,
            ]);
        });
    }

    /**
     * Update an owned report. The stored image is preserved unless a replacement
     * is uploaded, in which case the old file is removed only after the row has
     * been written.
     *
     * @param  array{title: string, description: string, category_id: int, location_id: int, event_date: string|null}  $data
     */
    public function updateReport(Item $item, array $data, ?UploadedFile $image): Item
    {
        return DB::transaction(function () use ($item, $data, $image): Item {
            $previousImage = $item->image_path;
            $storedPath = $image?->store(self::IMAGE_DIRECTORY, self::IMAGE_DISK);
            $newImage = $storedPath === false ? null : $storedPath;

            $item->fill($data);

            if ($newImage !== null) {
                $item->image_path = $newImage;
            }

            $item->save();

            if ($newImage !== null && $previousImage !== null) {
                $this->deleteImageFile($previousImage);
            }

            return $item;
        });
    }

    /**
     * Change an owned report's status after checking the transition is allowed
     * by the domain model.
     *
     * @throws ValidationException when the requested transition is not permitted
     */
    public function updateReportStatus(Item $item, ItemStatus $status): Item
    {
        if ($item->status === $status) {
            return $item;
        }

        if (! $item->status->canTransitionTo($status)) {
            throw ValidationException::withMessages([
                'status' => __('The report cannot be moved from :from to :to.', [
                    'from' => $item->status->value,
                    'to' => $status->value,
                ]),
            ]);
        }

        $item->update(['status' => $status]);

        return $item;
    }

    /**
     * Permanently delete an owned report and its image.
     *
     * Reports that already carry claims are refused: the claims table cascades
     * on item removal, and silently discarding claim history would destroy
     * another user's records.
     *
     * @throws ValidationException when the report still has dependent claims
     */
    public function deleteReport(Item $item): void
    {
        if ($item->claims()->exists()) {
            throw ValidationException::withMessages([
                'item' => __('This report has claims and cannot be deleted.'),
            ]);
        }

        $imagePath = $item->image_path;

        DB::transaction(fn (): bool => $item->delete());

        if ($imagePath !== null) {
            $this->deleteImageFile($imagePath);
        }
    }

    /**
     * Remove an image file if it lives inside the report image directory.
     * Paths outside that directory are left alone so a shared file is never
     * deleted by mistake.
     */
    private function deleteImageFile(string $path): void
    {
        if (! str_starts_with($path, self::IMAGE_DIRECTORY.'/')) {
            return;
        }

        Storage::disk(self::IMAGE_DISK)->delete($path);
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
     * @param  array{search?: string|null, status?: string|null, category?: int|null, location?: int|null}  $filters
     */
    private function status(array $filters): ?ItemStatus
    {
        $status = $filters['status'] ?? null;

        return is_string($status) ? ItemStatus::tryFrom($status) : null;
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
