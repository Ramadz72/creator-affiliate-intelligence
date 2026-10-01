<?php

namespace App\Services;

use App\Models\ImportBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        
        $currentBatchIds = $currentBatches
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        $comparisonBatchIds = $comparisonBatches
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        if (empty($currentBatchIds)) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Current Performances
        |--------------------------------------------------------------------------
        |
        | Ambil hanya kolom yang memang dibutuhkan oleh scoreRange().
        |
        */
        $currentQueryStart = microtime(true);

        $currentPerformances = DB::table('affiliate_performances')
            ->whereIn('import_batch_id', $currentBatchIds)
            ->select([
                'affiliate_id',
                'import_batch_id',
                'gmv',
                'attributed_orders',
                'buyers',
                'products_sold',
                'video_views',
                'impressions',
                'ctr',
                'ctor',
                'video_count',
                'live_count',
            ])
            ->get();
            Log::info('INSIGHTS PERFORMANCE', [
                'step' => 'current performance query',
                'seconds' => round(microtime(true) - $currentQueryStart, 3),
                'rows' => $currentPerformances->count(),
            ]);
            
        if ($currentPerformances->isEmpty()) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Comparison Performances
        |--------------------------------------------------------------------------
        */
        $comparisonQueryStart = microtime(true);

        $comparisonPerformances = collect();

        if (!empty($comparisonBatchIds)) {
            $comparisonPerformances = DB::table('affiliate_performances')
                ->whereIn('import_batch_id', $comparisonBatchIds)
                ->select([
                    'affiliate_id',
                    'import_batch_id',
                    'gmv',
                    'attributed_orders',
                    'buyers',
                    'products_sold',
                    'video_views',
                    'impressions',
                    'ctr',
                    'ctor',
                    'video_count',
                    'live_count',
                ])
                ->get();
                Log::info('INSIGHTS PERFORMANCE', [
                'step' => 'comparison performance query',
                'seconds' => round(microtime(true) - $comparisonQueryStart, 3),
                'rows' => $comparisonPerformances->count(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Dynamic Range Scores
        |--------------------------------------------------------------------------
        |
        | Ini tetap menggunakan engine scoring yang sama.
        |
        */

        $scoreStartedAt = microtime(true);

        $rangeScores = $this->scoreService->scoreRange(
            $currentPerformances,
            $comparisonPerformances
        );

        Log::info('INSIGHTS PERFORMANCE', [
            'step' => 'scoreRange',
            'seconds' => round(microtime(true) - $scoreStartedAt, 3),
            'current_rows' => $currentPerformances->count(),
            'comparison_rows' => $comparisonPerformances->count(),
            'scores' => $rangeScores->count(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Current Period Summary
        |--------------------------------------------------------------------------
        */

        $totalGmv = (float) $currentPerformances->sum('gmv');

        $totalOrders = (int) $currentPerformances->sum('attributed_orders');

        $totalProductsSold = (int) $currentPerformances->sum('products_sold');

        $affiliateCount = $currentPerformances
            ->pluck('affiliate_id')
            ->unique()
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Previous Period Summary
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
        | Affiliate Metadata
        |--------------------------------------------------------------------------
        */

        $metadataStart = microtime(true);

        $affiliateAggregates = DB::table('affiliate_performances as ap')
            ->join('affiliates as a', 'a.id', '=', 'ap.affiliate_id')
            ->whereIn('ap.import_batch_id', $currentBatchIds)
            ->select([
                'ap.affiliate_id',
                'a.name',
                'a.username',
            ])
            ->selectRaw('SUM(ap.gmv) as gmv')
            ->selectRaw('SUM(ap.attributed_orders) as orders')
            ->selectRaw('SUM(ap.products_sold) as products_sold')
            ->selectRaw('SUM(ap.video_views) as video_views')
            ->groupBy(
                'ap.affiliate_id',
                'a.name',
                'a.username'
            )
            ->get()
            ->keyBy('affiliate_id');

        Log::info('INSIGHTS PERFORMANCE', [
            'step' => 'affiliate aggregate + metadata',
            'seconds' => round(microtime(true) - $metadataStart, 3),
            'count' => $affiliateAggregates->count(),
        ]);

        $start = microtime(true);

        $affiliateData = $rangeScores->map(
            function ($score, $affiliateId) use ($affiliateAggregates) {
                $aggregate = $affiliateAggregates->get($affiliateId);

                if (!$aggregate) {
                    return null;
                }

                return [
                    'affiliate_id' => $affiliateId,

                    // Metadata
                    'name' => $aggregate->name,
                    'username' => $aggregate->username,

                    // Performance metrics
                    'gmv' => (float) $aggregate->gmv,
                    'orders' => (int) $aggregate->orders,
                    'products_sold' => (int) $aggregate->products_sold,
                    'video_views' => (int) $aggregate->video_views,

                    // Scores
                    'performance_score' => $score['performance_score'] ?? null,
                    'growth_score' => $score['growth_score'] ?? null,
                    'consistency_score' => $score['consistency_score'] ?? null,
                    'opportunity_score' => $score['opportunity_score'] ?? null,
                    'overall_score' => $score['overall_score'] ?? null,

                    // Growth
                    'growth_percent' => $score['growth_percent'] ?? null,

                    // Period
                    'period_count' => $score['period_count'] ?? 0,

                    // Action
                    'action' => $score['action'] ?? null,
                ];
            }
        )->filter()->values();

        Log::info('INSIGHTS PERFORMANCE', [
            'step' => 'affiliate data mapping',
            'seconds' => round(microtime(true) - $start, 3),
            'count' => $affiliateData->count(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Top GMV
        |--------------------------------------------------------------------------
        */

        $start = microtime(true);

        $topGmv = $affiliateData
            ->sortByDesc('gmv')
            ->take(5)
            ->values()
            ->all();

        Log::info('INSIGHTS PERFORMANCE', [
            'step' => 'top gmv',
            'seconds' => round(microtime(true) - $start, 3),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Top Performers
        |--------------------------------------------------------------------------
        |
        | Tetap mempertahankan behavior sebelumnya:
        | Top Performer = top GMV.
        |
        */

        $topPerformers = $topGmv;

        /*
        |--------------------------------------------------------------------------
        | Action Counts
        |--------------------------------------------------------------------------
        */

        $chaseCount = 0;
        $supportCount = 0;
        $deprioritizeCount = 0;

        /*
        |--------------------------------------------------------------------------
        | Attention
        |--------------------------------------------------------------------------
        */

        $start = microtime(true);

        $monitoring = collect();
        $potential = collect();

        foreach ($affiliateData as $affiliate) {
            $action = $affiliate['action'];

            if ($action === 'CHASE') {
                $chaseCount++;
            }

            if ($action === 'SUPPORT') {
                $supportCount++;
            }

            if ($action === 'DEPRIORITIZE') {
                $deprioritizeCount++;
            }

            /*
            |--------------------------------------------------------------------------
            | High GMV, Low Consistency
            |--------------------------------------------------------------------------
            */

            if (
                $affiliate['gmv'] >= 5_000_000
                && $affiliate['consistency_score'] !== null
                && $affiliate['consistency_score'] < 40
            ) {
                $monitoring->push($affiliate);
            }

            /*
            |--------------------------------------------------------------------------
            | Potential Opportunity
            |--------------------------------------------------------------------------
            */

            if (
                $affiliate['gmv'] < 5_000_000
                && $affiliate['opportunity_score'] !== null
                && $affiliate['opportunity_score'] >= 70
            ) {
                $potential->push($affiliate);
            }
        }

        Log::info('INSIGHTS PERFORMANCE', [
            'step' => 'attention',
            'seconds' => round(microtime(true) - $start, 3),
            'monitoring' => $monitoring->count(),
            'potential' => $potential->count(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Sort Attention
        |--------------------------------------------------------------------------
        */

        $monitoring = $monitoring
            ->sortByDesc('gmv')
            ->values();

        $potential = $potential
            ->sortByDesc('opportunity_score')
            ->values();

        $monitoringCount = $monitoring->count();
        $potentialCount = $potential->count();

        /*
        |--------------------------------------------------------------------------
        | Insight Summary
        |--------------------------------------------------------------------------
        */

        $insightSummary = [];

        /*
        |--------------------------------------------------------------------------
        | Top Performer Insight
        |--------------------------------------------------------------------------
        */

        if (!empty($topPerformers)) {
            $top = $topPerformers[0];

            $insightSummary[] = [
                'type' => 'top_performer',
                'title' => 'Top Performer',

                'headline' => ($top['name'] ?? 'Affiliate')
                    . ' menjadi kontributor GMV terbesar periode ini.',

                'description' => 'Menghasilkan GMV sebesar Rp'
                    . number_format($top['gmv'], 0, ',', '.')
                    . ' dengan '
                    . number_format($top['orders'], 0, ',', '.')
                    . ' orders.',

                'recommended_action' =>
                    'Pertahankan performa dan evaluasi peluang pengembangan lebih lanjut.',

                'affiliate_id' => $top['affiliate_id'],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Monitoring Insight
        |--------------------------------------------------------------------------
        */

        if ($monitoringCount > 0) {
            $insightSummary[] = [
                'type' => 'monitoring',
                'title' => 'High GMV, Low Consistency',

                'headline' => $monitoringCount
                    . ' affiliate memiliki GMV tinggi tetapi consistency rendah.',

                'description' =>
                    'Kondisi ini menunjukkan performa yang perlu dipantau agar kontribusi GMV tetap berkelanjutan.',

                'recommended_action' =>
                    'Pantau konsistensi konten dan performa pada periode berikutnya.',

                'count' => $monitoringCount,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Potential Insight
        |--------------------------------------------------------------------------
        */

        if ($potentialCount > 0) {
            $insightSummary[] = [
                'type' => 'potential',
                'title' => 'Potential Opportunity',

                'headline' => $potentialCount
                    . ' affiliate memiliki opportunity score tinggi dengan GMV di bawah Rp5 juta.',

                'description' =>
                    'Affiliate dalam kategori ini menunjukkan sinyal yang layak diperhatikan meskipun kontribusi GMV masih relatif kecil.',

                'recommended_action' =>
                    'Pertimbangkan pengembangan, aktivasi konten, atau dukungan tambahan.',

                'count' => $potentialCount,
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
                $affiliateCount,
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

                'recommended_action' =>
                    'Gunakan perubahan antarperiode sebagai dasar evaluasi strategi affiliate.',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Period
        |--------------------------------------------------------------------------
        */

        $currentStart = $currentBatches->min('period_start');
        $currentEnd = $currentBatches->max('period_end');

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
                'affiliate_count' => $affiliateCount,

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
                            $affiliateCount,
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
                'chase_count' => $chaseCount,
                'support_count' => $supportCount,
                'deprioritize_count' => $deprioritizeCount,
            ],

            'attention' => [
                'monitoring' => $monitoring
                    ->take(8)
                    ->values()
                    ->all(),

                'monitoring_count' => $monitoringCount,

                'potential' => $potential
                    ->take(8)
                    ->values()
                    ->all(),

                'potential_count' => $potentialCount,

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
        $engineStartedAt = microtime(true);

        Log::info('INSIGHTS PERFORMANCE', [
            'step' => 'engine complete',
            'seconds' => round(microtime(true) - $engineStartedAt, 3),
        ]);
        
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