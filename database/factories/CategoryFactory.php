<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * A pool of plausible item classifications.
     *
     * @var array<int, string>
     */
    protected const array NAMES = [
        'ID Cards and Documents',
        'Electronics',
        'Wallets',
        'Bags',
        'Keys',
        'Clothing',
        'Accessories',
        'Books and Stationery',
        'Eyewear',
        'Other Items',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(static::NAMES);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
