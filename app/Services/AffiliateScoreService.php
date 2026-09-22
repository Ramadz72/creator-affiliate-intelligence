<?php

namespace App\Services;

use App\Models\AffiliatePerformance;
use App\Models\AffiliateScore;
use App\Models\ImportBatch;
use Illuminate\Support\Collection;

class AffiliateScoreService
{
    /**
     * Generate score untuk seluruh affiliate dalam satu batch.
     */
    public function scoreBatch(ImportBatch $batch): int
    {
        $performances = AffiliatePerformance::query()
            ->where('import_batch_id', $batch->id)
            ->get();

        if ($performances->isEmpty()) {
            return 0;
        }

        /*
         * Performance terbaru.
         * Semua affiliate dalam batch dibandingkan satu sama lain.
         */
        $percentiles = [
            'gmv' => $this->buildPercentileMap($performances->pluck('gmv')),
            'orders' => $this->buildPercentileMap(
                $performances->pluck('attributed_orders')
            ),
            'buyers' => $this->buildPercentileMap(
                $performances->pluck('buyers')
            ),
            'content' => $this->buildPercentileMap(
                $performances->map(
                    fn ($item) =>
                        (int) $item->video_count +
                        (int) $item->live_count
                )
            ),
            'ctr' => $this->buildPercentileMap($performances->pluck('ctr')),
            'ctor' => $this->buildPercentileMap($performances->pluck('ctor')),
        ];

        /*
         * Ambil batch sebelumnya untuk menghitung Growth.
         */
        $previousBatch = ImportBatch::query()
            ->where('status', 'completed')
            ->where('id', '<', $batch->id)
            ->latest('id')
            ->first();

        $previousPerformances = collect();

        if ($previousBatch) {
            $previousPerformances = AffiliatePerformance::query()
                ->where('import_batch_id', $previousBatch->id)
                ->get()
                ->keyBy('affiliate_id');
        }

        /*
         * Ambil seluruh histori GMV untuk menghitung Consistency.
         */
        $affiliateIds = $performances
            ->pluck('affiliate_id')
            ->unique()
            ->values();

        $history = AffiliatePerformance::query()
            ->whereIn('affiliate_id', $affiliateIds)
            ->whereHas('importBatch', function ($query) {
                $query->where('status', 'completed');
            })
            ->orderBy('import_batch_id')
            ->get()
            ->groupBy('affiliate_id');

        $now = now();
        $rows = [];

        foreach ($performances as $performance) {
            $affiliateId = $performance->affiliate_id;

            /*
             * ============================
             * PERFORMANCE SCORE
             * ============================
             *
             * Semua metric menggunakan percentile,
             * bukan dibandingkan langsung dengan affiliate #1.
             */

            $gmvScore = $percentiles['gmv'][
                $this->normalizeKey($performance->gmv)
            ] ?? 0;

            $ordersScore = $percentiles['orders'][
                $this->normalizeKey($performance->attributed_orders)
            ] ?? 0;

            $buyersScore = $percentiles['buyers'][
                $this->normalizeKey($performance->buyers)
            ] ?? 0;

            $contentActivity =
                (int) $performance->video_count +
                (int) $performance->live_count;

            $contentScore = $percentiles['content'][
                $this->normalizeKey($contentActivity)
            ] ?? 0;

            $ctrScore = $percentiles['ctr'][
                $this->normalizeKey($performance->ctr)
            ] ?? 0;

            $ctorScore = $percentiles['ctor'][
                $this->normalizeKey($performance->ctor)
            ] ?? 0;

            $engagementScore = (
                $ctrScore * 0.50
            ) + (
                $ctorScore * 0.50
            );

            $performanceScore = round(
                ($gmvScore * 0.40) +
                ($ordersScore * 0.25) +
                ($buyersScore * 0.15) +
                ($contentScore * 0.10) +
                ($engagementScore * 0.10),
                2
            );

            /*
             * ============================
             * GROWTH SCORE
             * ============================
             */

            $growthScore = 0;
            $growthPercent = null;

            $previous = $previousPerformances->get($affiliateId);

            if ($previous) {
                $previousGmv = (float) $previous->gmv;
                $currentGmv = (float) $performance->gmv;

                if ($previousGmv > 0) {
                    $growthPercent =
                        (($currentGmv - $previousGmv) / $previousGmv) * 100;

                    /*
                     * -50%  = 0
                     *   0%  = 50
                     * +50%  = 100
                     */
                    $growthScore = min(
                        max(50 + $growthPercent, 0),
                        100
                    );

                    $growthScore = round($growthScore, 2);
                }
            }

            /*
             * ============================
             * CONSISTENCY SCORE
             * ============================
             */

            $consistencyScore = 0;

            $affiliateHistory = $history->get($affiliateId, collect());

            if ($affiliateHistory->count() >= 2) {
                $gmvs = $affiliateHistory
                    ->pluck('gmv')
                    ->map(fn ($value) => (float) $value)
                    ->filter(fn ($value) => $value > 0)
                    ->values();

                if ($gmvs->count() >= 2) {
                    $average = $gmvs->avg();

                    if ($average > 0) {
                        $variance = $gmvs
                            ->map(
                                fn ($value) =>
                                    pow($value - $average, 2)
                            )
                            ->avg();

                        $standardDeviation = sqrt($variance);

                        $coefficientVariation =
                            $standardDeviation / $average;

                        $consistencyScore = round(
                            max(
                                0,
                                min(
                                    100,
                                    100 -
                                    ($coefficientVariation * 100)
                                )
                            ),
                            2
                        );
                    }
                }
            }

            /*
             * ============================
             * OPPORTUNITY SCORE
             * ============================
             *
             * Jika baru satu periode:
             * Opportunity = Performance.
             *
             * Jika sudah >= 2 periode:
             * Performance 50%
             * Growth 30%
             * Consistency 20%
             */

            if ($previousBatch) {
                $opportunityScore = round(
                    ($performanceScore * 0.50) +
                    ($growthScore * 0.30) +
                    ($consistencyScore * 0.20),
                    2
                );
            } else {
                $opportunityScore = $performanceScore;
            }

            $action = $this->determineAction(
                $opportunityScore
            );

            $rows[] = [
                'affiliate_id' => $affiliateId,
                'import_batch_id' => $batch->id,
                'performance_score' => $performanceScore,
                'growth_score' => $growthScore,
                'consistency_score' => $consistencyScore,
                'opportunity_score' => $opportunityScore,
                'overall_score' => $opportunityScore,
                'action' => $action,
                'generated_at' => $now,
            ];
        }

        /*
         * Upsert sekaligus dalam chunk supaya jauh lebih ringan
         * dibanding updateOrCreate satu per satu.
         */
        foreach (array_chunk($rows, 500) as $chunk) {
            AffiliateScore::upsert(
                $chunk,
                ['affiliate_id', 'import_batch_id'],
                [
                    'performance_score',
                    'growth_score',
                    'consistency_score',
                    'opportunity_score',
                    'overall_score',
                    'action',
                    'generated_at',
                ]
            );
        }

        return count($rows);
    }

    /**
     * Membuat percentile berdasarkan seluruh nilai
     * dalam satu batch.
     */
    private function buildPercentileMap(Collection $values): array
    {
        $unique = $values
            ->map(fn ($value) => $this->normalizeKey($value))
            ->unique()
            ->sort()
            ->values();

        $count = $unique->count();

        if ($count <= 1) {
            return [
                $unique->first() ?? '0' => 100,
            ];
        }

        $map = [];

        foreach ($unique as $index => $value) {
            $map[$value] = round(
                ($index / ($count - 1)) * 100,
                2
            );
        }

        return $map;
    }

    /**
     * Menyamakan format key angka supaya lookup percentile konsisten.
     */
    private function normalizeKey(mixed $value): string
    {
        return number_format(
            (float) $value,
            4,
            '.',
            ''
        );
    }

    /**
     * Menentukan action berdasarkan Opportunity Score.
     */
    private function determineAction(float $opportunityScore): string
    {
        if ($opportunityScore >= 75) {
            return 'CHASE';
        }

        if ($opportunityScore >= 55) {
            return 'SUPPORT';
        }

        if ($opportunityScore >= 35) {
            return 'MONITOR';
        }

        return 'DEPRIORITIZE';
    }
}