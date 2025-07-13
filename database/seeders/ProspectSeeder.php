<?php

namespace Database\Seeders;

use App\Models\Prospect;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProspectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $prospects = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'telephone' => '876-555-1234',
            ],
            [
                'name' => 'Sarah Brown',
                'email' => 'sarah.brown@example.com',
                'telephone' => '876-555-5678',
            ],
            [
                'name' => 'Michael Johnson',
                'email' => 'michael.johnson@example.com',
                'telephone' => '876-555-9012',
            ],
        ];

        foreach ($prospects as $data) {
            Prospect::create($data);
        }

    }
}
