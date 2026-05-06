<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationResource extends JsonResource
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
            'patient' => [
                'age' => $this->patient_age,
                'sex' => $this->patient_sex,
            ],
            'chief_complaint' => $this->chief_complaint,
            'duration_days' => $this->duration_days,
            'symptoms' => $this->symptoms,
            'notes' => $this->notes,
            'status' => $this->status,
            'images' => ImageResource::collection($this->whenLoaded('images', $this->images)),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            // Relationships could be added here later (aiResult)
        ];
    }
}
