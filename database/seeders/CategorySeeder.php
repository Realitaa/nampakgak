<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
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

        foreach ($names as $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}
