<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Example 1: Admin user
        User::create([
            'first_name' => 'Brian',
            'last_name' => 'Findlay',
            'email' => 'brian@mochacoders.com',
            'password' => Hash::make('password123'), // Use bcrypt or Hash::make
        ]);

        // You can add more users here...
    }
}
