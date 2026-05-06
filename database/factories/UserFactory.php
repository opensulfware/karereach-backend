<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->unique()->phoneNumber(),
            'pin' => Hash::make('123456'),
            'region' => fake()->city(),
            'health_program_id' => 'HP-' . fake()->numberBetween(100, 999),
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }
}
