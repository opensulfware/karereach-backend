<?php

namespace App\Services\Ai;

use App\Ai\Agents\ClinicalTriageAgent;
use App\Contracts\AiServiceContract;
use App\Models\Consultation;
use Illuminate\Support\Facades\Log;

class TriageAiService implements AiServiceContract
{
    protected $promptBuilder;

    public function __construct(
        ClinicalPromptBuilder $promptBuilder
    ) {
        $this->promptBuilder = $promptBuilder;
    }

    /**
     * Analyse a consultation using the configured AI provider (Ollama, Groq, etc.) via laravel/ai Agents.
     */
    public function analyse(Consultation $consultation): array
    {
        $prompt = $this->promptBuilder->build($consultation);
        
        // Get provider and model from config/env
        $provider = config('ai.default', env('AI_PROVIDER', 'ollama'));
        $model = config("ai.providers.{$provider}.model", env('AI_MODEL', 'medgemma:4b'));

        try {
            // Using the proper laravel/ai Agent pattern
            $agent = new ClinicalTriageAgent();
            
            // The prompt() method handles provider switching seamlessly
            $response = $agent->prompt(
                prompt: $prompt,
                provider: $provider,
                model: $model,
                timeout: 60
            );

            $structured = $response->structured;

            if (empty($structured)) {
                throw new \Exception("AI Provider [{$provider}] failed to provide a structured response");
            }

            return [
                'probable_conditions' => $structured['probable_conditions'] ?? [],
                'risk_level' => $structured['risk_level'] ?? 'urgent',
                'next_action' => $structured['next_action'] ?? 'refer',
                'clinical_notes' => $structured['clinical_notes'] ?? 'Analysis completed.',
                'red_flags' => $structured['red_flags'] ?? [],
                'ollama_model' => $model, // Keeping the key as ollama_model for DB compatibility or renaming it in a migration later
            ];

        } catch (\Throwable $e) {
            Log::error("KareReach AI Analysis Failed [Provider: {$provider}]: " . $e->getMessage());

            // Robust Fallback
            return [
                'probable_conditions' => ['Uncertain'],
                'risk_level' => 'urgent',
                'next_action' => 'refer',
                'clinical_notes' => 'AI analysis failed. Defaulting to safe referral protocol.',
                'red_flags' => ['Analysis Error'],
                'ollama_model' => $model,
                'error' => true
            ];
        }
    }
}
