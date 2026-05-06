<?php

namespace App\Contracts;

use App\Models\Consultation;

interface AiServiceContract
{
    /**
     * Analyse a consultation using the AI engine.
     *
     * @param Consultation $consultation
     * @return array The structured AI analysis results.
     */
    public function analyse(Consultation $consultation): array;
}
