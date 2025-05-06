<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                'name' => 'admin',
                'username' => 'admin',
                'email' => 'admin@email.com',
                'password' => bcrypt('admin'),
                'role' => 'admin',
                'phone' => '12345678910',
                'address' => 'admin address',
                'city' => 'admin city',
                'state' => 'admin state',
                'country' => 'admin country',
                'postal_code' => 'admin postal code',
            ],
            [
                'name' => 'user1',
                'username' => 'user1',
                'email' => 'user1@email.com',
                'password' => bcrypt('user1'),
                'role' => 'user',
                'phone' => '0987654321',
                'address' => 'user1 address',
                'city' => 'user1 city',
                'state' => 'user1 state',
                'country' => 'user1 country',
                'postal_code' => 'user1 postal code',
            ],
        ];

        foreach ($users as $user) {
            User::factory()->create($user);
        }
    }
}
