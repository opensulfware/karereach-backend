<?php

namespace App\Http\Controllers\Api\v1;

use App\Constants\SystemCode;
use App\Contracts\AiServiceContract;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\v1\AIResultResource;
use App\Models\Consultation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AIAnalysisController extends ApiController
{
    public function __construct(
        protected AiServiceContract $aiService
    ) {}

    /**
     * Trigger AI analysis for a consultation.
     */
    public function analyse(Request $request, $id): JsonResponse
    {
        $consultation = $request->user()->consultations()->find($id);

        if (!$consultation) {
            return $this->error('Consultation not found', 404, null, SystemCode::ERR_CONSULTATION_NOT_FOUND);
        }

        // Prevent redundant analysis if already completed
        if ($consultation->status === 'completed' && $consultation->aiResult) {
            return $this->success(new AIResultResource($consultation->aiResult), 'Consultation already analysed');
        }

        $startTime = microtime(true);
        $analysis = $this->aiService->analyse($consultation);
        $responseTime = round((microtime(true) - $startTime) * 1000);

        try {
            $result = DB::transaction(function () use ($consultation, $analysis, $responseTime) {
                // Update consultation status
                $consultation->update(['status' => 'completed']);

                // Create or Update AI Result
                return $consultation->aiResult()->updateOrCreate(
                    ['consultation_id' => $consultation->id],
                    [
                        'probable_conditions' => $analysis['probable_conditions'],
                        'risk_level' => $analysis['risk_level'],
                        'next_action' => $analysis['next_action'],
                        'clinical_notes' => $analysis['clinical_notes'],
                        'red_flags' => $analysis['red_flags'],
                        'ollama_model' => $analysis['ollama_model'],
                        'ollama_response_time_ms' => $responseTime,
                    ]
                );
            });

            return $this->success(new AIResultResource($result), 'Analysis completed successfully', 201, SystemCode::AI_ANALYSIS_SUCCESS);

        } catch (\Throwable $e) {
            return $this->error('Failed to save AI analysis results', 500, $e->getMessage(), SystemCode::ERR_AI_ANALYSIS_FAILED);
        }
    }

    /**
     * Fetch AI result for a consultation.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $consultation = $request->user()->consultations()->with('aiResult')->find($id);

        if (!$consultation) {
            return $this->error('Consultation not found', 404, null, SystemCode::ERR_CONSULTATION_NOT_FOUND);
        }

        if (!$consultation->aiResult) {
            return $this->error('AI analysis not yet performed', 404);
        }

        return $this->success(new AIResultResource($consultation->aiResult), 'AI result retrieved successfully');
    }
}
