<?php

namespace Database\Factories;

use App\Enums\ItemStatus;
use App\Enums\ItemType;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Loss and discovery contexts used to compose descriptions.
     *
     * @var array<int, string>
     */
    protected const array SCENARIOS = [
        'Reported lost after the last lecture session of the day.',
        'Found unattended and handed to the FMIPA front desk.',
        'Left behind after a laboratory practicum.',
        'Discovered while cleaning up after a study group meeting.',
        'Realized missing on the way back to the dormitory.',
        'Handed over by a classmate who found it in the corridor.',
        'Last seen during a group assignment discussion.',
        'Found near the entrance and kept for safekeeping.',
        'Noticed missing after the afternoon break.',
        'Recovered from a seat in the lecture hall.',
        'Brought to the security post after the building emptied.',
        'Missing after attending a student organization event.',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'location_id' => Location::factory(),
            'title' => Str::limit(fake()->sentence(3), 60, ''),
            'type' => fake()->randomElement(ItemType::cases()),
            'description' => fake()->randomElement(static::SCENARIOS),
            'image_path' => fake()->optional(0.6)->passthrough('items/placeholder-'.fake()->numerify('#####').'.jpg'),
            'status' => ItemStatus::Active,
            'event_date' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }

    /**
     * Indicate that the item is a lost-item report.
     */
    public function lost(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ItemType::Lost,
        ]);
    }

    /**
     * Indicate that the item is a found-item report.
     */
    public function found(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ItemType::Found,
        ]);
    }

    /**
     * Indicate that the item is still awaiting a match.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ItemStatus::Active,
        ]);
    }

    /**
     * Indicate that the item is under claim review.
     */
    public function claimed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ItemStatus::Claimed,
        ]);
    }

    /**
     * Indicate that the item has been returned or otherwise resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ItemStatus::Resolved,
        ]);
    }
}
