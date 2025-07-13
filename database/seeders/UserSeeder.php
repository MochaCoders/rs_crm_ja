<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {

        $client = Client::first();
        $adminAgentRole = Role::where('name', 'admin_agent')->first();

        // Example 1: Admin user
        User::create([
            'first_name' => 'Brian',
            'last_name' => 'Findlay',
            'email' => 'brian@mochacoders.com',
            'password' => Hash::make('password123'), // Use bcrypt or Hash::make
            'client_id' => $client->id,
            'role_id' => $adminAgentRole->id,
        ]);

        // You can add more users here...
    }
}
