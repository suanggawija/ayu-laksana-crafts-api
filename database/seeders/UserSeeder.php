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
        User::factory()->create([
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
        ]);
    }
}
