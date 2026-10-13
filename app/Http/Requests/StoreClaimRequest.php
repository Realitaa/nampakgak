<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClaimRequest extends FormRequest
{
    /**
     * Only authentication is checked here; ownership, report type, status, and
     * duplicate rules are enforced by ClaimService.
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
            'proof_description' => ['required', 'string', 'max:2000'],
            'proof_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'proof_image.mimes' => 'The image must be a JPG, JPEG, or PNG file.',
            'proof_image.max' => 'The image may not be larger than 2 MB.',
        ];
    }

    /**
     * @return array{proof_description: string}
     */
    public function claimData(): array
    {
        return ['proof_description' => (string) $this->validated('proof_description')];
    }
}
