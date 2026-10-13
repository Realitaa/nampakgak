<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin NampakGak',
                'email' => 'admin@nampakgak.test',
                'phone_number' => '081200000001',
                'role' => 'admin',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@nampakgak.test',
                'phone_number' => '081200000002',
                'role' => 'user',
            ],
            [
                'name' => 'Siti Rahayu',
                'email' => 'siti@nampakgak.test',
                'phone_number' => '081200000003',
                'role' => 'user',
            ],
            [
                'name' => 'Andi Pratama',
                'email' => 'andi@nampakgak.test',
                'phone_number' => '081200000004',
                'role' => 'user',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi@nampakgak.test',
                'phone_number' => null,
                'role' => 'user',
            ],
        ];

        foreach ($users as $user) {
            // Only the initial password is fixed; existing accounts are left untouched.
            User::firstOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'phone_number' => $user['phone_number'],
                    'role' => $user['role'],
                    'email_verified_at' => now(),
                    'password' => 'password',
                ],
            );
        }
    }
}
