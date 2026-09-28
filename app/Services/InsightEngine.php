<?php

namespace App\Services;

use App\Models\AffiliatePerformance;
use App\Models\AffiliateScore;
use App\Models\ImportBatch;

class InsightEngine
{
    public function generateForBatch(ImportBatch $batch): array
    {
        $performances = AffiliatePerformance::query()
            ->where('import_batch_id', $batch->id)
            ->with('affiliate')
            ->get();

        $scores = AffiliateScore::query()
            ->where('import_batch_id', $batch->id)
            ->get()
            ->keyBy('affiliate_id');

        $previousBatch = ImportBatch::query()
            ->where('uploaded_by', $batch->uploaded_by)
            ->where('status', 'completed')
            ->whereNotNull('period_start')
            ->whereNotNull('period_end')
            ->whereDate('period_end', '<', $batch->period_start)
            ->orderByDesc('period_end')
            ->first();

        $totalGmv = $performances->sum('gmv');
        $totalOrders = $performances->sum('attributed_orders');
        $totalProductsSold = $performances->sum('products_sold');

        $previousSummary = null;

        if ($previousBatch) {
            $previousPerformances = AffiliatePerformance::query()
                ->where('import_batch_id', $previousBatch->id)
                ->get();

            $previousSummary = [
                'total_gmv' => (float) $previousPerformances->sum('gmv'),
                'total_orders' => (int) $previousPerformances->sum('attributed_orders'),
                'total_products_sold' => (int) $previousPerformances->sum('products_sold'),
                'affiliate_count' => $previousPerformances->count(),
            ];
        }

        $affiliateData = $performances->map(function ($performance) use ($scores) {
            $score = $scores->get($performance->affiliate_id);

            return [
                'affiliate_id' => $performance->affiliate_id,
                'name' => $performance->affiliate?->name,
                'username' => $performance->affiliate?->username,

                'gmv' => (float) $performance->gmv,
                'orders' => (int) $performance->attributed_orders,
                'products_sold' => (int) $performance->products_sold,
                'video_views' => (int) $performance->video_views,

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

        $topGmv = $affiliateData
            ->sortByDesc('gmv')
            ->take(5)
            ->values()
            ->all();

        $topPerformers = $topGmv;

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
        
        $monitoring = $affiliateData
            ->filter(function ($affiliate) {
                return $affiliate['gmv'] >= 5_000_000
                    && $affiliate['consistency_score'] !== null
                    && $affiliate['consistency_score'] < 40;
            })
            ->sortByDesc('gmv')
            ->values()
            ->all();

        $potential = $affiliateData
            ->filter(function ($affiliate) {
                return $affiliate['gmv'] < 5_000_000
                    && $affiliate['opportunity_score'] !== null
                    && $affiliate['opportunity_score'] >= 70;
            })
            ->sortByDesc('opportunity_score')
            ->values()
            ->all();

        $insightSummary = [];

            if (!empty($topPerformers)) {
                $top = $topPerformers[0];

                $insightSummary[] = [
                    'type' => 'top_performer',
                    'title' => 'Top Performer',
                    'headline' => $top['name'] . ' menjadi kontributor GMV terbesar periode ini.',
                    'description' => 'Menghasilkan GMV sebesar Rp' . number_format($top['gmv'], 0, ',', '.') . ' dengan ' . number_format($top['orders'], 0, ',', '.') . ' orders.',
                    'recommended_action' => 'Pertahankan performa dan evaluasi peluang pengembangan lebih lanjut.',
                    'affiliate_id' => $top['affiliate_id'],
                ];
            }

            if (!empty($monitoring)) {
                $insightSummary[] = [
                    'type' => 'monitoring',
                    'title' => 'High GMV, Low Consistency',
                    'headline' => count($monitoring) . ' affiliate memiliki GMV tinggi tetapi consistency rendah.',
                    'description' => 'Kondisi ini menunjukkan performa yang perlu dipantau agar kontribusi GMV tetap berkelanjutan.',
                    'recommended_action' => 'Pantau konsistensi konten dan performa pada periode berikutnya.',
                    'count' => count($monitoring),
                ];
            }

            if (!empty($potential)) {
                $insightSummary[] = [
                    'type' => 'potential',
                    'title' => 'Potential Opportunity',
                    'headline' => count($potential) . ' affiliate memiliki opportunity score tinggi dengan GMV di bawah Rp5 juta.',
                    'description' => 'Affiliate dalam kategori ini menunjukkan sinyal yang layak diperhatikan meskipun kontribusi GMV masih relatif kecil.',
                    'recommended_action' => 'Pertimbangkan pengembangan, aktivasi konten, atau dukungan tambahan.',
                    'count' => count($potential),
                ];
            }

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
                            : ($gmvComparison['direction'] === 'down' ? 'menurun' : 'tetap'),
                        abs($gmvComparison['percentage'] ?? 0)
                    ),
                    'description' => sprintf(
                        'Orders %s %.1f%%, produk terjual %s %.1f%%, dan jumlah affiliate %s %.1f%%.',
                        $ordersComparison['direction'] === 'up'
                            ? 'meningkat'
                            : ($ordersComparison['direction'] === 'down' ? 'menurun' : 'tetap'),
                        abs($ordersComparison['percentage'] ?? 0),
                        $productsComparison['direction'] === 'up'
                            ? 'meningkat'
                            : ($productsComparison['direction'] === 'down' ? 'menurun' : 'tetap'),
                        abs($productsComparison['percentage'] ?? 0),
                        $affiliateComparison['direction'] === 'up'
                            ? 'meningkat'
                            : ($affiliateComparison['direction'] === 'down' ? 'menurun' : 'tetap'),
                        abs($affiliateComparison['percentage'] ?? 0),
                    ),
                    'recommended_action' => 'Gunakan perubahan antarperiode sebagai dasar evaluasi strategi affiliate.',
                ];
            }
            

        return [
            'period' => [
                'start' => $batch->period_start?->format('Y-m-d'),
                'end' => $batch->period_end?->format('Y-m-d'),
            ],

            'summary' => [
                'total_gmv' => (float) $totalGmv,
                'total_orders' => (int) $totalOrders,
                'total_products_sold' => (int) $totalProductsSold,
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

                'previous_period' => $previousBatch
                    ? [
                        'start' => $previousBatch->period_start?->format('Y-m-d'),
                        'end' => $previousBatch->period_end?->format('Y-m-d'),
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