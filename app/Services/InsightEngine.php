<?php

namespace App\Services;

use App\Models\AffiliatePerformance;
use App\Models\ImportBatch;
use Illuminate\Support\Collection;

class InsightEngine
{
    public function __construct(
        private AffiliateScoreService $scoreService
    ) {
    }

    /**
     * Generate insight berdasarkan periode/range.
     */
    public function generateForPeriod(
        Collection $currentBatches,
        Collection $comparisonBatches
    ): ?array {
        if ($currentBatches->isEmpty()) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Current Performances
        |--------------------------------------------------------------------------
        */

        $currentPerformances = AffiliatePerformance::query()
            ->whereIn(
                'import_batch_id',
                $currentBatches->pluck('id')
            )
            ->with('affiliate')
            ->get();

        if ($currentPerformances->isEmpty()) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Comparison Performances
        |--------------------------------------------------------------------------
        */

        $comparisonPerformances = $comparisonBatches->isNotEmpty()
            ? AffiliatePerformance::query()
                ->whereIn(
                    'import_batch_id',
                    $comparisonBatches->pluck('id')
                )
                ->get()
            : collect();

        /*
        |--------------------------------------------------------------------------
        | Dynamic Range Scores
        |--------------------------------------------------------------------------
        |
        | Sama dengan Affiliate Index:
        | - Performance
        | - Growth
        | - Consistency
        | - Opportunity
        | - Action
        |
        */

        $rangeScores = $this->scoreService->scoreRange(
            $currentPerformances->map(function ($performance) {
                return $performance;
            }),
            $comparisonPerformances
        );

        /*
        |--------------------------------------------------------------------------
        | Aggregate Current Period
        |--------------------------------------------------------------------------
        */

        $totalGmv = (float) $currentPerformances->sum('gmv');
        $totalOrders = (int) $currentPerformances->sum('attributed_orders');
        $totalProductsSold = (int) $currentPerformances->sum('products_sold');

        /*
        |--------------------------------------------------------------------------
        | Aggregate Previous Period
        |--------------------------------------------------------------------------
        */

        $previousSummary = null;

        if ($comparisonPerformances->isNotEmpty()) {
            $previousSummary = [
                'total_gmv' => (float) $comparisonPerformances->sum('gmv'),
                'total_orders' => (int) $comparisonPerformances->sum('attributed_orders'),
                'total_products_sold' => (int) $comparisonPerformances->sum('products_sold'),
                'affiliate_count' => $comparisonPerformances
                    ->pluck('affiliate_id')
                    ->unique()
                    ->count(),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Affiliate Data
        |--------------------------------------------------------------------------
        |
        | Karena sekarang bisa ada banyak snapshot harian,
        | jangan langsung membuat 1 row per performance.
        |
        | Kita aggregate per affiliate terlebih dahulu.
        |
        */

        $affiliateGroups = $currentPerformances
            ->groupBy('affiliate_id');

        $affiliateData = $affiliateGroups
            ->map(function (Collection $performances, $affiliateId) use ($rangeScores) {
                $first = $performances->first();

                $score = $rangeScores->get($affiliateId);

                return [
                    'affiliate_id' => $affiliateId,

                    'name' => $first?->affiliate?->name,
                    'username' => $first?->affiliate?->username,

                    'gmv' => (float) $performances->sum('gmv'),
                    'orders' => (int) $performances->sum('attributed_orders'),
                    'products_sold' => (int) $performances->sum('products_sold'),
                    'video_views' => (int) $performances->sum('video_views'),

                    'performance_score' => $score['performance_score'] ?? null,
                    'growth_score' => $score['growth_score'] ?? null,
                    'consistency_score' => $score['consistency_score'] ?? null,
                    'opportunity_score' => $score['opportunity_score'] ?? null,
                    'overall_score' => $score['overall_score'] ?? null,

                    'growth_percent' => $score['growth_percent'] ?? null,
                    'period_count' => $score['period_count'] ?? 0,

                    'action' => $score['action'] ?? null,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Top GMV
        |--------------------------------------------------------------------------
        */

        $topGmv = $affiliateData
            ->sortByDesc('gmv')
            ->take(5)
            ->values()
            ->all();

        $topPerformers = $topGmv;

        /*
        |--------------------------------------------------------------------------
        | Actions
        |--------------------------------------------------------------------------
        */

        $chase = $affiliateData
            ->where('action', 'CHASE')
            ->sortByDesc('opportunity_score')
            ->values()
            ->all();

        $support = $affiliateData
            ->where('action', 'SUPPORT')
            ->sortByDesc('opportunity_score')
            ->values()
            ->all();

        $deprioritize = $affiliateData
            ->where('action', 'DEPRIORITIZE')
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | High GMV, Low Consistency
        |--------------------------------------------------------------------------
        */

        $monitoring = $affiliateData
            ->filter(function ($affiliate) {
                return $affiliate['gmv'] >= 5_000_000
                    && $affiliate['consistency_score'] !== null
                    && $affiliate['consistency_score'] < 40;
            })
            ->sortByDesc('gmv')
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Potential Opportunity
        |--------------------------------------------------------------------------
        */

        $potential = $affiliateData
            ->filter(function ($affiliate) {
                return $affiliate['gmv'] < 5_000_000
                    && $affiliate['opportunity_score'] !== null
                    && $affiliate['opportunity_score'] >= 70;
            })
            ->sortByDesc('opportunity_score')
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Insight Summary
        |--------------------------------------------------------------------------
        */

        $insightSummary = [];

        /*
        |--------------------------------------------------------------------------
        | Top Performer
        |--------------------------------------------------------------------------
        */

        if (!empty($topPerformers)) {
            $top = $topPerformers[0];

            $insightSummary[] = [
                'type' => 'top_performer',
                'title' => 'Top Performer',
                'headline' => $top['name']
                    . ' menjadi kontributor GMV terbesar periode ini.',
                'description' => 'Menghasilkan GMV sebesar Rp'
                    . number_format($top['gmv'], 0, ',', '.')
                    . ' dengan '
                    . number_format($top['orders'], 0, ',', '.')
                    . ' orders.',
                'recommended_action' => 'Pertahankan performa dan evaluasi peluang pengembangan lebih lanjut.',
                'affiliate_id' => $top['affiliate_id'],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | High GMV, Low Consistency
        |--------------------------------------------------------------------------
        */

        if (!empty($monitoring)) {
            $insightSummary[] = [
                'type' => 'monitoring',
                'title' => 'High GMV, Low Consistency',
                'headline' => count($monitoring)
                    . ' affiliate memiliki GMV tinggi tetapi consistency rendah.',
                'description' => 'Kondisi ini menunjukkan performa yang perlu dipantau agar kontribusi GMV tetap berkelanjutan.',
                'recommended_action' => 'Pantau konsistensi konten dan performa pada periode berikutnya.',
                'count' => count($monitoring),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Potential Opportunity
        |--------------------------------------------------------------------------
        */

        if (!empty($potential)) {
            $insightSummary[] = [
                'type' => 'potential',
                'title' => 'Potential Opportunity',
                'headline' => count($potential)
                    . ' affiliate memiliki opportunity score tinggi dengan GMV di bawah Rp5 juta.',
                'description' => 'Affiliate dalam kategori ini menunjukkan sinyal yang layak diperhatikan meskipun kontribusi GMV masih relatif kecil.',
                'recommended_action' => 'Pertimbangkan pengembangan, aktivasi konten, atau dukungan tambahan.',
                'count' => count($potential),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Performance Movement
        |--------------------------------------------------------------------------
        */

        if ($previousSummary) {
            $gmvComparison = $this->compareMetric(
                $totalGmv,
                $previousSummary['total_gmv']
            );

            $ordersComparison = $this->compareMetric(
                $totalOrders,
                $previousSummary['total_orders']
            );

            $productsComparison = $this->compareMetric(
                $totalProductsSold,
                $previousSummary['total_products_sold']
            );

            $affiliateComparison = $this->compareMetric(
                $affiliateData->count(),
                $previousSummary['affiliate_count']
            );

            $insightSummary[] = [
                'type' => 'performance_movement',
                'title' => 'Performance Movement',

                'headline' => sprintf(
                    'GMV %s %.1f%% dibanding periode sebelumnya.',
                    $gmvComparison['direction'] === 'up'
                        ? 'meningkat'
                        : ($gmvComparison['direction'] === 'down'
                            ? 'menurun'
                            : 'tetap'),
                    abs($gmvComparison['percentage'] ?? 0)
                ),

                'description' => sprintf(
                    'Orders %s %.1f%%, produk terjual %s %.1f%%, dan jumlah affiliate %s %.1f%%.',
                    $ordersComparison['direction'] === 'up'
                        ? 'meningkat'
                        : ($ordersComparison['direction'] === 'down'
                            ? 'menurun'
                            : 'tetap'),
                    abs($ordersComparison['percentage'] ?? 0),

                    $productsComparison['direction'] === 'up'
                        ? 'meningkat'
                        : ($productsComparison['direction'] === 'down'
                            ? 'menurun'
                            : 'tetap'),
                    abs($productsComparison['percentage'] ?? 0),

                    $affiliateComparison['direction'] === 'up'
                        ? 'meningkat'
                        : ($affiliateComparison['direction'] === 'down'
                            ? 'menurun'
                            : 'tetap'),
                    abs($affiliateComparison['percentage'] ?? 0),
                ),

                'recommended_action' => 'Gunakan perubahan antarperiode sebagai dasar evaluasi strategi affiliate.',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Period
        |--------------------------------------------------------------------------
        */

        $currentStart = $currentBatches
            ->min('period_start');

        $currentEnd = $currentBatches
            ->max('period_end');

        $previousStart = $comparisonBatches->isNotEmpty()
            ? $comparisonBatches->min('period_start')
            : null;

        $previousEnd = $comparisonBatches->isNotEmpty()
            ? $comparisonBatches->max('period_end')
            : null;

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return [
            'period' => [
                'start' => $currentStart?->format('Y-m-d'),
                'end' => $currentEnd?->format('Y-m-d'),
            ],

            'summary' => [
                'total_gmv' => $totalGmv,
                'total_orders' => $totalOrders,
                'total_products_sold' => $totalProductsSold,

                'affiliate_count' => $affiliateData->count(),

                'comparison' => [
                    'gmv' => $previousSummary
                        ? $this->compareMetric(
                            $totalGmv,
                            $previousSummary['total_gmv']
                        )
                        : null,

                    'orders' => $previousSummary
                        ? $this->compareMetric(
                            $totalOrders,
                            $previousSummary['total_orders']
                        )
                        : null,

                    'products_sold' => $previousSummary
                        ? $this->compareMetric(
                            $totalProductsSold,
                            $previousSummary['total_products_sold']
                        )
                        : null,

                    'affiliate_count' => $previousSummary
                        ? $this->compareMetric(
                            $affiliateData->count(),
                            $previousSummary['affiliate_count']
                        )
                        : null,
                ],

                'previous_period' => $previousStart
                    ? [
                        'start' => $previousStart->format('Y-m-d'),
                        'end' => $previousEnd->format('Y-m-d'),
                    ]
                    : null,
            ],

            'top_gmv' => $topGmv,

            'actions' => [
                'chase' => $chase,
                'support' => $support,
                'deprioritize' => $deprioritize,
            ],

            'attention' => [
                'monitoring' => $monitoring,
                'potential' => $potential,
                'top_performers' => $topPerformers,
            ],

            'insights' => $insightSummary,
        ];
    }

    /**
     * Backward compatibility untuk pemanggilan lama.
     */
    public function generateForBatch(ImportBatch $batch): ?array
    {
        return $this->generateForPeriod(
            collect([$batch]),
            collect()
        );
    }

    private function compareMetric(
        float|int $current,
        float|int $previous
    ): array {
        $difference = $current - $previous;

        return [
            'current' => $current,
            'previous' => $previous,
            'difference' => $difference,

            'percentage' => $previous != 0
                ? round(($difference / $previous) * 100, 1)
                : null,

            'direction' => $difference > 0
                ? 'up'
                : ($difference < 0 ? 'down' : 'flat'),
        ];
    }
}