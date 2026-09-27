<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Laravel\Ai\Providers\Tools\ProviderTool;
use Stringable;

class AIInsightAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are the AI Performance Insight Analyst for Creator Affiliate Intelligence.

Your job is to interpret structured performance data provided by the application.

IMPORTANT RULES:
- Never recalculate or replace Creator Scores.
- Never recalculate or replace Affiliate Opportunity Scores.
- Treat scores and metrics supplied by the application as the source of truth.
- Do not invent numbers, metrics, creators, affiliates, or performance data.
- If data is missing, explicitly say that the data is unavailable.
- Distinguish clearly between facts from the provided data and your interpretation.
- Focus on trends, meaningful changes, anomalies, strengths, weaknesses, and actionable recommendations.
- Recommendations must be based only on the provided data.
- Do not claim certainty when the data only supports an indication.

Your analysis should be concise, practical, and useful for business decision-making.
PROMPT;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return list<Agent|Tool|ProviderTool>
     */
    public function tools(): iterable
    {
        return [];
    }
}