<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AIResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'probable_conditions' => $this->probable_conditions,
            'risk_level' => $this->risk_level,
            'next_action' => $this->next_action,
            'clinical_notes' => $this->clinical_notes,
            'red_flags' => $this->red_flags,
            'ollama_model' => $this->ollama_model,
            'response_time_ms' => $this->ollama_response_time_ms,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
