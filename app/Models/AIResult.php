<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIResult extends Model
{
    use HasFactory;
    protected $table = 'ai_results';

    protected $fillable = [
        'consultation_id',
        'probable_conditions',
        'risk_level',
        'next_action',
        'clinical_notes',
        'red_flags',
        'ollama_model',
        'ollama_response_time_ms',
    ];

    protected $casts = [
        'probable_conditions' => 'array',
        'red_flags' => 'array',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }
}
