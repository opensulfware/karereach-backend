<?php

namespace Database\Seeders;

use App\Models\Consultation;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConsultationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('phone', '+237600000001')->first();

        if (!$user) return;

        $samples = [
            [
                'patient_age' => 24,
                'patient_sex' => 'female',
                'chief_complaint' => 'Severe headache and blurred vision for 2 days.',
                'duration_days' => 2,
                'symptoms' => ['headache', 'vision_blur', 'nausea'],
                'notes' => 'Patient has history of hypertension.',
                'status' => 'completed',
            ],
            [
                'patient_age' => 5,
                'patient_sex' => 'male',
                'chief_complaint' => 'Persistent cough and high fever.',
                'duration_days' => 3,
                'symptoms' => ['cough', 'fever', 'breathing_difficulty'],
                'notes' => 'Chest sounding wheezy.',
                'status' => 'completed',
            ],
            [
                'patient_age' => 45,
                'patient_sex' => 'female',
                'chief_complaint' => 'Sharp abdominal pain after meals.',
                'duration_days' => 7,
                'symptoms' => ['abdominal_pain', 'bloating'],
                'notes' => 'Pain is localized in the upper right quadrant.',
                'status' => 'draft',
            ],
            [
                'patient_age' => 12,
                'patient_sex' => 'male',
                'chief_complaint' => 'Itchy rash across the back and arms.',
                'duration_days' => 1,
                'symptoms' => ['rash', 'itching'],
                'notes' => 'Possibly allergic reaction.',
                'status' => 'completed',
            ],
            [
                'patient_age' => 30,
                'patient_sex' => 'male',
                'chief_complaint' => 'Painful swelling in the left ankle.',
                'duration_days' => 1,
                'symptoms' => ['pain', 'swelling'],
                'notes' => 'Occurred after a fall.',
                'status' => 'completed',
            ],
        ];

        foreach ($samples as $sample) {
            $user->consultations()->create($sample);
        }
    }
}
