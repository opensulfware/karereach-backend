<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class ClinicalTriageAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return "You are a clinical assistant for Community Health Workers. 
        Your task is to analyze patient data (age, sex, symptoms, notes) and provide a structured clinical triage. 
        Be conservative, prioritize safety, and flag any medical emergencies immediately.";
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'probable_conditions' => $schema->array()->items($schema->string())->required(),
            'risk_level' => $schema->string()->enum(['low', 'urgent', 'emergency'])->required(),
            'next_action' => $schema->string()->enum(['refer', 'treat', 'monitor'])->required(),
            'clinical_notes' => $schema->string()->required(),
            'red_flags' => $schema->array()->items($schema->string())->required(),
        ];
    }
}
