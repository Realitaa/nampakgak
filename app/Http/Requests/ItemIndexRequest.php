<?php

namespace App\Http\Requests;

use App\Enums\ItemType;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class ItemIndexRequest extends FormRequest
{
    /**
     * Browsing reports is public, so no authorization is required.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * No query string rule rejects the request. Filter values are sanitized in
     * filters() so a stale or unknown value never bounces a bookmarked URL.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Normalized filter values, dropping anything invalid so the listing still
     * renders instead of returning a validation error.
     *
     * @return array{search: string|null, type: string|null, category: int|null, location: int|null}
     */
    public function filters(): array
    {
        return [
            'search' => $this->searchTerm(),
            'type' => ItemType::tryFrom((string) $this->query('type', ''))?->value,
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
     * Return the record id when the value points at an existing row, otherwise null.
     *
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
