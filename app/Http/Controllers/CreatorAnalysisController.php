<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use App\Models\CreatorScore;
use App\Models\CreatorAnalysisSnapshot;
use App\Services\CreatorAnalysisService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CreatorAnalysisController extends Controller
{
    /**
     * Menampilkan analysis TERAKHIR yang sudah tersimpan.
     *
     * Penting:
     * Halaman ini TIDAK menghitung ulang analysis.
     */
    public function show(Creator $creator)
    {
        abort_unless($creator->user_id === Auth::id(), 404);

        $score = CreatorScore::where('creator_id', $creator->id)
            ->orderByDesc('id')
            ->first();

        if (!$score) {
            return Inertia::render('creators/Analysis', [
                'creator' => $creator,
                'analysis' => null,
            ]);
        }

        $scoreHistory = CreatorScore::where('creator_id', $creator->id)
        ->orderBy('period_end')
        ->orderBy('id')
        ->get([
            'id',
            'period_start',
            'period_end',
            'performance_score',
            'engagement_score',
            'audience_fit_score',
            'historical_score',
            'deal_value_score',
            'overall_score',
            'recommendation',
        ]);

        $snapshot = CreatorAnalysisSnapshot::where(
            'creator_score_id',
            $score->id
        )->first();

        if (!$snapshot) {
            return Inertia::render('creators/Analysis', [
                'creator' => $creator,
                'analysis' => null,
            ]);
        }

        $analysis = [
            'creator_id' => $creator->id,
            'creator_name' => $creator->name,

            'period_start' => $score->period_start,
            'period_end' => $score->period_end,

            'content_count' => $snapshot->content_count,
            'average_views' => $snapshot->average_views,
            'engagement_rate' => $snapshot->engagement_rate,

            'performance_score' => $score->performance_score,
            'engagement_score' => $score->engagement_score,
            'audience_fit_score' => $score->audience_fit_score,
            'historical_score' => $score->historical_score,
            'deal_value_score' => $score->deal_value_score,

            'overall_score' => $score->overall_score,
            'recommendation' => $score->recommendation,

            'average_roas' => $snapshot->average_roas ?? null,

            'rate_card' => $snapshot->rate_card_platform
                ? [
                    'platform' => $snapshot->rate_card_platform,
                    'deliverable' => $snapshot->rate_card_deliverable,
                    'price' => (float) $snapshot->rate_card_price,
                ]
                : null,

            'cost_per_view' => $snapshot->cost_per_view,

            'insights' => $snapshot->insights ?? [],
        ];

        return Inertia::render('creators/Analysis', [
            'creator' => $creator,
            'analysis' => $analysis,
            'scoreHistory' => $scoreHistory,
        ]);
    }

    /**
     * Menjalankan analysis baru.
     *
     * Hanya endpoint ini yang menghitung ulang score.
     */
    public function run(
        Creator $creator,
        CreatorAnalysisService $analysisService
    ) {
        abort_unless($creator->user_id === Auth::id(), 404);

        $analysis = $analysisService->analyze($creator);

        if (
            !$analysis['period_start'] ||
            !$analysis['period_end']
        ) {
            return redirect()
                ->route('creators.analysis', $creator)
                ->with('error', 'Creator belum memiliki content yang bisa dianalisis.');
        }

        $creatorScore = CreatorScore::create([
            'creator_id' => $creator->id,
            'period_start' => $analysis['period_start'],
            'period_end' => $analysis['period_end'],
            'performance_score' => $analysis['performance_score'],
            'engagement_score' => $analysis['engagement_score'],
            'audience_fit_score' => $analysis['audience_fit_score'],
            'historical_score' => $analysis['historical_score'],
            'deal_value_score' => $analysis['deal_value_score'],
            'overall_score' => $analysis['overall_score'],
            'recommendation' => $analysis['recommendation'],
        ]);

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

        return redirect()
            ->route('creators.analysis', $creator)
            ->with('success', 'Analysis berhasil diperbarui.');
    }

    /**
     * History semua analysis creator.
     */
    public function history(Creator $creator)
    {
        abort_unless($creator->user_id === Auth::id(), 404);

        $history = CreatorScore::where('creator_id', $creator->id)
            ->orderByDesc('period_end')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('creators/AnalysisHistory', [
            'creator' => $creator,
            'history' => $history,
        ]);
    }

    /**
     * Detail snapshot analysis tertentu.
     */
    public function historyDetail(
        Creator $creator,
        CreatorScore $score
    ) {
        abort_unless($score->creator_id === $creator->id, 404);
        abort_unless($creator->user_id === Auth::id(), 404);

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