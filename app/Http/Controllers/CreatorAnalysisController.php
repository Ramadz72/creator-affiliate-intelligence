<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use App\Models\CreatorScore;
use App\Models\CreatorAnalysisSnapshot;
use App\Services\CreatorAnalysisService;
use Inertia\Inertia;

class CreatorAnalysisController extends Controller
{
    public function show(
        Creator $creator,
        CreatorAnalysisService $analysisService
    ) {
        $analysis = $analysisService->analyze($creator);

        /*
        |--------------------------------------------------------------------------
        | Simpan hasil analisis
        |--------------------------------------------------------------------------
        */

        if (
            $analysis['period_start'] &&
            $analysis['period_end']
        ) {
            $creatorScore = CreatorScore::updateOrCreate(
            [
                'creator_id' => $creator->id,
                'period_start' => $analysis['period_start'],
                'period_end' => $analysis['period_end'],
            ],
            [
                'performance_score' => $analysis['performance_score'],
                'engagement_score' => $analysis['engagement_score'],
                'audience_fit_score' => $analysis['audience_fit_score'],
                'historical_score' => $analysis['historical_score'],
                'deal_value_score' => $analysis['deal_value_score'],
                'overall_score' => $analysis['overall_score'],
                'recommendation' => $analysis['recommendation'],
            ]
        );

            CreatorAnalysisSnapshot::updateOrCreate(
            [
                'creator_score_id' => $creatorScore->id,
            ],
            [
                'content_count' => $analysis['content_count'],
                'average_views' => $analysis['average_views'],
                'engagement_rate' => $analysis['engagement_rate'],
                'rate_card_platform' => $analysis['rate_card']['platform'] ?? null,
                'rate_card_deliverable' => $analysis['rate_card']['deliverable'] ?? null,
                'rate_card_price' => $analysis['rate_card']['price'] ?? null,
                'cost_per_view' => $analysis['cost_per_view'] ?? null,
                'insights' => $analysis['insights'] ?? [],
            ]
        );
        }

        return Inertia::render('creators/Analysis', [
            'creator' => $creator,
            'analysis' => $analysis,
        ]);
    }

    public function history(Creator $creator)
    {
        $history = CreatorScore::where('creator_id', $creator->id)
            ->orderByDesc('period_end')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('creators/AnalysisHistory', [
            'creator' => $creator,
            'history' => $history,
        ]);
    }
    
    public function historyDetail(
    Creator $creator,
    CreatorScore $score
    ) {
        abort_unless($score->creator_id === $creator->id, 404);

        $snapshot = CreatorAnalysisSnapshot::where(
            'creator_score_id',
            $score->id
        )->firstOrFail();

        return Inertia::render('creators/AnalysisSnapshot', [
            'creator' => $creator,
            'score' => $score,
            'snapshot' => $snapshot,
        ]);
    }
}