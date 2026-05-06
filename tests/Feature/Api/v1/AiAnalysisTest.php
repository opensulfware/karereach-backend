<?php

namespace Tests\Feature\Api\v1;

use App\Ai\Agents\ClinicalTriageAgent;
use App\Constants\SystemCode;
use App\Models\Consultation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\Data\Meta;
use Tests\TestCase;

class AiAnalysisTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test triggering AI analysis with faked response.
     */
    public function test_chw_can_trigger_ai_analysis(): void
    {
        // Fake the ClinicalTriageAgent response with a sequential array of responses
        ClinicalTriageAgent::fake([
            [
                'probable_conditions' => ['Malaria', 'Flu'],
                'risk_level' => 'urgent',
                'next_action' => 'refer',
                'clinical_notes' => 'Symptoms highly suggestive of malaria.',
                'red_flags' => ['High fever'],
            ]
        ]);

        $user = User::factory()->create();
        $consultation = Consultation::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/consultations/{$consultation->id}/analyse");

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'code' => SystemCode::AI_ANALYSIS_SUCCESS,
                'data' => [
                    'risk_level' => 'urgent',
                    'next_action' => 'refer',
                ],
            ]);

        $this->assertDatabaseHas('consultations', [
            'id' => $consultation->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('ai_results', [
            'consultation_id' => $consultation->id,
            'risk_level' => 'urgent',
        ]);
    }

    /**
     * Test retrieving AI results.
     */
    public function test_chw_can_retrieve_ai_result(): void
    {
        $user = User::factory()->create();
        $consultation = Consultation::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
        ]);
        
        $consultation->aiResult()->create([
            'probable_conditions' => ['Malaria'],
            'risk_level' => 'urgent',
            'next_action' => 'refer',
            'clinical_notes' => 'Test notes',
            'red_flags' => [],
            'ollama_model' => 'medgemma',
            'ollama_response_time_ms' => 1200,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/consultations/{$consultation->id}/result");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'risk_level' => 'urgent',
                ],
            ]);
    }
}
