<?php

namespace App\Http\Requests;

use App\Enums\ItemStatus;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class LostItemIndexRequest extends FormRequest
{
    /**
     * The route is already behind the auth middleware.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Filter values are sanitized in filters() rather than rejected, so a stale
     * bookmark or an old link still renders the listing.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array{search: string|null, status: string|null, category: int|null, location: int|null}
     */
    public function filters(): array
    {
        return [
            'search' => $this->searchTerm(),
            'status' => ItemStatus::tryFrom((string) $this->query('status', ''))?->value,
            'category' => $this->existingId(Category::class, $this->query('category')),
            'location' => $this->existingId(Location::class, $this->query('location')),
        ];
    }

    private function searchTerm(): ?string
    {
        $term = trim((string) $this->query('search', ''));

        return $term === '' ? null : mb_substr($term, 0, 100);
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function existingId(string $model, mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $id = (int) $value;

        return $model::query()->whereKey($id)->exists() ? $id : null;
    }
}
