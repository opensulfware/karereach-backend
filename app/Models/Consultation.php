<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'patient_age',
        'patient_sex',
        'chief_complaint',
        'duration_days',
        'symptoms',
        'notes',
        'status',
    ];

    protected $casts = [
        'symptoms' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ConsultationImage::class);
    }

    public function aiResult(): HasOne
    {
        return $this->hasOne(AIResult::class);
    }
}
