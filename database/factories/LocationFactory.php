<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    /**
     * Plausible locations around the UNIMED / FMIPA campus.
     *
     * @var array<int, string>
     */
    protected const array NAMES = [
        'FMIPA Building A Lobby',
        'FMIPA Building B Lobby',
        'FMIPA Dean\'s Office',
        'Biology Study Program Office',
        'Chemistry Laboratory',
        'Physics Laboratory',
        'Mathematics Lecture Hall 1',
        'Mathematics Lecture Hall 2',
        'Computer Laboratory',
        'FMIPA Library',
        'Campus Cafeteria',
        'FMIPA Parking Area',
        'Main Campus Field',
        'Sports Hall',
        'Student Center',
        'Main Library',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(static::NAMES),
        ];
    }
}
