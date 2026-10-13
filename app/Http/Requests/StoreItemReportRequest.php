<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemReportRequest extends FormRequest
{
    /**
     * Any authenticated user may report an item they found or lost. The report
     * type is decided by the controller route, never by the request payload.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category_id' => ['required', 'integer', Rule::exists(Category::class, 'id')],
            'location_id' => ['required', 'integer', Rule::exists(Location::class, 'id')],
            'event_date' => ['nullable', 'date', 'before_or_equal:today'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image.mimes' => 'The image must be a JPG, JPEG, or PNG file.',
            'image.max' => 'The image may not be larger than 2 MB.',
        ];
    }

    /**
     * Validated payload without the file or any ownership/type fields; those are
     * assigned server-side by the service.
     *
     * @return array{title: string, description: string, category_id: int, location_id: int, event_date: string|null}
     */
    public function reportData(): array
    {
        $validated = $this->validated();

        return [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category_id' => (int) $validated['category_id'],
            'location_id' => (int) $validated['location_id'],
            'event_date' => $validated['event_date'] ?? null,
        ];
    }
}
