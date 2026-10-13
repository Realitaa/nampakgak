<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
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

        foreach ($names as $name) {
            Location::updateOrCreate(['name' => $name]);
        }
    }
}
