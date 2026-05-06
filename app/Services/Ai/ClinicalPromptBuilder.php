<?php

namespace App\Services\Ai;

use App\Models\Consultation;

class ClinicalPromptBuilder
{
    /**
     * Build a summary of patient data for the Agent.
     */
    public function build(Consultation $consultation): string
    {
        $patientInfo = "Patient: {$consultation->patient_age} year old " . ucfirst($consultation->patient_sex);
        $symptoms = implode(', ', $consultation->symptoms);
        
        $data = "{$patientInfo}\n";
        $data .= "Chief Complaint: {$consultation->chief_complaint}\n";
        $data .= "Duration: {$consultation->duration_days} days\n";
        $data .= "Reported Symptoms: {$symptoms}\n";
        
        if ($consultation->notes) {
            $data .= "Clinical Notes: {$consultation->notes}\n";
        }

        return $data;
    }
}
