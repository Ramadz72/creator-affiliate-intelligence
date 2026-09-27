<?php

namespace App\Services;

use App\Ai\Agents\AIInsightAgent;
use App\Models\AffiliatePerformance;
use App\Models\AffiliateScore;
use App\Models\ImportBatch;
use Laravel\Ai\Enums\Lab;

class AIInsightService
{
    public function generateForBatch(ImportBatch $batch): string
    {
        $performances = AffiliatePerformance::query()
            ->where('import_batch_id', $batch->id)
            ->with('affiliate')
            ->get();

        $scores = AffiliateScore::query()
            ->where('import_batch_id', $batch->id)
            ->get()
            ->keyBy('affiliate_id');

        $totalGmv = $performances->sum('gmv');
        $totalOrders = $performances->sum('attributed_orders');
        $totalProductsSold = $performances->sum('products_sold');

        $affiliateData = $performances->map(function ($performance) use ($scores) {
            $score = $scores->get($performance->affiliate_id);

            return [
                'name' => $performance->affiliate?->name,
                'username' => $performance->affiliate?->username,
                'gmv' => (float) $performance->gmv,
                'orders' => (int) $performance->attributed_orders,
                'products_sold' => (int) $performance->products_sold,
                'video_views' => (int) $performance->video_views,
                'commission' => (float) $performance->commission,

                // Existing scoring engine — source of truth.
                'performance_score' => $score?->performance_score !== null
                    ? (float) $score->performance_score
                    : null,

                'growth_score' => $score?->growth_score !== null
                    ? (float) $score->growth_score
                    : null,

                'consistency_score' => $score?->consistency_score !== null
                    ? (float) $score->consistency_score
                    : null,

                'opportunity_score' => $score?->opportunity_score !== null
                    ? (float) $score->opportunity_score
                    : null,

                'overall_score' => $score?->overall_score !== null
                    ? (float) $score->overall_score
                    : null,

                'action' => $score?->action,
            ];
        });

        $topAffiliates = $affiliateData
            ->sortByDesc('gmv')
            ->take(10)
            ->values()
            ->all();

        $highestOpportunity = $affiliateData
            ->filter(fn ($affiliate) => $affiliate['opportunity_score'] !== null)
            ->sortByDesc('opportunity_score')
            ->take(10)
            ->values()
            ->all();

        $lowestOpportunity = $affiliateData
            ->filter(fn ($affiliate) => $affiliate['opportunity_score'] !== null)
            ->sortBy('opportunity_score')
            ->take(10)
            ->values()
            ->all();

        $payload = [
            'period' => [
                'start' => $batch->period_start?->format('Y-m-d'),
                'end' => $batch->period_end?->format('Y-m-d'),
            ],
            'summary' => [
                'total_gmv' => $totalGmv,
                'total_orders' => $totalOrders,
                'total_products_sold' => $totalProductsSold,
                'affiliate_count' => $affiliateData->count(),
            ],
            'top_affiliates_by_gmv' => $topAffiliates,
            'highest_opportunity_affiliates' => $highestOpportunity,
            'lowest_opportunity_affiliates' => $lowestOpportunity,
        ];

        $prompt = <<<'PROMPT'
Analyze the following affiliate performance data for Creator Affiliate Intelligence.

The application has already calculated all Affiliate Scores.
Those scores are the source of truth.

IMPORTANT:
- Do NOT recalculate any score.
- Do NOT invent missing metrics.
- Do NOT invent historical data.
- Use the opportunity_score, overall_score, performance_score,
  growth_score, consistency_score, and action exactly as provided.
- Distinguish facts from interpretation.
- Focus on useful business insights.
- Mention important strengths, weaknesses, anomalies, and opportunities.
- Give practical recommended actions.
- If information is unavailable, explicitly say so.

Return the analysis using these sections:

1. Performance Summary
2. Important Changes or Patterns
3. Potential Concerns
4. Recommended Actions

DATA:
PROMPT;

        $prompt .= "\n" . json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );

        $agent = new AIInsightAgent();

        $response = $agent->prompt(
            $prompt,
            provider: Lab::Gemini,
            model: 'gemini-flash-latest',
        );

        return (string) $response;
    }
}