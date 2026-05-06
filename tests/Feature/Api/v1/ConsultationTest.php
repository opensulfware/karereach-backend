<?php

namespace Tests\Feature\Api\v1;

use App\Constants\SystemCode;
use App\Models\Consultation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConsultationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a consultation.
     */
    public function test_chw_can_create_consultation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/consultations', [
                'patient_age' => 25,
                'patient_sex' => 'female',
                'chief_complaint' => 'Persistent cough',
                'duration_days' => 5,
                'symptoms' => ['fever', 'cough', 'fatigue'],
                'notes' => 'Patient looks weak.',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'code' => SystemCode::CONSULTATION_CREATED,
            ]);

        $this->assertDatabaseHas('consultations', [
            'user_id' => $user->id,
            'chief_complaint' => 'Persistent cough',
            'status' => 'draft',
        ]);
    }

    /**
     * Test retrieving a list of consultations.
     */
    public function test_chw_can_list_their_consultations(): void
    {
        $user = User::factory()->create();
        Consultation::factory()->count(3)->create(['user_id' => $user->id]);
        
        // Another user's consultation
        Consultation::factory()->create(['user_id' => User::factory()->create()->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/consultations');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.consultations')
            ->assertJson([
                'code' => SystemCode::CONSULTATION_RETRIEVED,
            ]);
    }

    /**
     * Test image upload.
     */
    public function test_chw_can_upload_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create(['user_id' => $user->id]);

        $file1 = UploadedFile::fake()->image('eye1.jpg');
        $file2 = UploadedFile::fake()->image('eye2.png');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/consultations/{$consultation->id}/images", [
                'images' => [$file1, $file2],
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'code' => SystemCode::IMAGE_UPLOAD_SUCCESS,
            ]);

        $this->assertEquals(2, $consultation->images()->count());
        Storage::disk('public')->assertExists($consultation->images()->first()->file_path);
    }

    /**
     * Test image upload fails with too many images.
     */
    public function test_image_upload_fails_with_too_many_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create(['user_id' => $user->id]);

        $files = [
            UploadedFile::fake()->image('img1.jpg'),
            UploadedFile::fake()->image('img2.jpg'),
            UploadedFile::fake()->image('img3.jpg'),
            UploadedFile::fake()->image('img4.jpg'),
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/consultations/{$consultation->id}/images", [
                'images' => $files,
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test image upload fails with invalid file type.
     */
    public function test_image_upload_fails_with_invalid_type(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create(['user_id' => $user->id]);

        $file = UploadedFile::fake()->create('document.pdf', 500);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/consultations/{$consultation->id}/images", [
                'images' => [$file],
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test CHW cannot upload images to another CHW's consultation.
     */
    public function test_chw_cannot_upload_to_others_consultation(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $consultation = Consultation::factory()->create(['user_id' => $user1->id]);

        $file = UploadedFile::fake()->image('eye.jpg');

        $response = $this->actingAs($user2, 'sanctum')
            ->postJson("/api/v1/consultations/{$consultation->id}/images", [
                'images' => [$file],
            ]);

        $response->assertStatus(404); // Scoped finding returns 404
    }
}
