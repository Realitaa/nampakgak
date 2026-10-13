<?php

namespace Database\Factories;

use App\Enums\ClaimStatus;
use App\Models\Claim;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Claim>
 */
class ClaimFactory extends Factory
{
    /**
     * Plausible ownership evidence descriptions.
     *
     * @var array<int, string>
     */
    protected const array EVIDENCE = [
        'The item has my initials written on the inside of the cover.',
        'I can describe the exact contents that were inside it at the time.',
        'There is a small scratch on the bottom edge from a previous fall.',
        'The wallpaper is a photo of my family, which I can show as proof.',
        'I still have the original receipt and the matching serial number.',
        'A sticker with my student organization logo is attached to the back.',
        'I can name the exact compartment where I keep my spare keys.',
        'The lock screen shows a calendar reminder for my class schedule.',
        'My name is written in permanent marker on the inner tag.',
        'I can describe the unique keychain attached to the strap.',
    ];

    /**
     * Reasons used when a claim is rejected.
     *
     * @var array<int, string>
     */
    protected const array REJECTION_REASONS = [
        'The provided details did not match the item kept by the finder.',
        'The evidence described could apply to several similar items.',
        'The claimant could not confirm a distinguishing feature on request.',
        'A previous claimant already provided matching proof of ownership.',
        'The claim was submitted for an item that was not reported as found.',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => Item::factory()->found(),
            'claimant_id' => User::factory(),
            'proof_description' => fake()->randomElement(static::EVIDENCE),
            'proof_image' => fake()->optional(0.5)->passthrough('claims/proof-'.fake()->numerify('#####').'.jpg'),
            'status' => ClaimStatus::Pending,
            'rejection_reason' => null,
        ];
    }

    /**
     * Indicate that the claim is awaiting review.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ClaimStatus::Pending,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the claim has been accepted.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ClaimStatus::Approved,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the claim has been declined with a reason.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ClaimStatus::Rejected,
            'rejection_reason' => fake()->randomElement(static::REJECTION_REASONS),
        ]);
    }
}
