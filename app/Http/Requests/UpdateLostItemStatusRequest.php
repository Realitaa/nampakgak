<?php

namespace App\Http\Requests;

use App\Enums\ItemStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateLostItemStatusRequest extends FormRequest
{
    /**
     * Authorization is handled by the LostItemPolicy in the controller.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * The value must be a real status; the allowed transition is enforced by
     * ItemStatus::canTransitionTo() inside the service so an invalid move can
     * return a clear message instead of a generic validation error.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(ItemStatus::class)],
        ];
    }

    public function status(): ItemStatus
    {
        return ItemStatus::from((string) $this->validated('status'));
    }
}
