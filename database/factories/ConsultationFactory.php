<?php

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Consultation>
 */
class ConsultationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'patient_age' => fake()->numberBetween(1, 80),
            'patient_sex' => fake()->randomElement(['male', 'female']),
            'chief_complaint' => fake()->sentence(),
            'duration_days' => fake()->numberBetween(1, 30),
            'symptoms' => fake()->randomElements(['fever', 'cough', 'headache', 'fatigue', 'nausea'], 3),
            'notes' => fake()->paragraph(),
            'status' => 'draft',
        ];
    }
}
