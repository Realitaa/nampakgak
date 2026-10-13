<?php

namespace Database\Factories;

use App\Models\FoundNotification;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FoundNotification>
 */
class FoundNotificationFactory extends Factory
{
    /**
     * Notes a finder might leave for the person who lost the item.
     *
     * @var array<int, string>
     */
    protected const array NOTES = [
        'I think I found this near the campus cafeteria this morning. It matches the description.',
        'Kept it safe at the FMIPA front desk after finding it in the lecture hall.',
        'Found it in the library reading room. Happy to hand it over after you confirm the details.',
        'I picked it up from the parking area and left it with the security post.',
        'Spotted this in the laboratory corridor and secured it before someone else took it.',
        'It is currently with me. Let me know a good time to return it on campus.',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => Item::factory()->lost(),
            'reporter_id' => User::factory(),
            'note' => fake()->randomElement(static::NOTES),
            'image_path' => null,
        ];
    }
}
