<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFoundNotificationRequest extends FormRequest
{
    /**
     * Only authentication is checked here; the report's eligibility and the
     * self-report rule are enforced by FoundNotificationService, which returns
     * a clear message instead of a policy 403.
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
            'note' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
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
     * @return array{note: string}
     */
    public function notificationData(): array
    {
        return ['note' => (string) $this->validated('note')];
    }
}
