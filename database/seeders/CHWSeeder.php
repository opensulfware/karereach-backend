<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CHWSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Amara Diallo',
            'phone' => '+237600000001',
            'pin' => Hash::make('123456'),
            'region' => 'Littoral',
            'health_program_id' => 'HP-001',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Fatima Ouedraogo',
            'phone' => '+237600000002',
            'pin' => Hash::make('654321'),
            'region' => 'Center',
            'health_program_id' => 'HP-002',
            'is_active' => true,
        ]);
    }
}
