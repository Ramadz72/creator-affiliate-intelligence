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

            $previous = $previousByAffiliate->get($affiliateId);

            if ($previous && $previous['gmv'] > 0) {
                $growthPercent = (
                    ($target['gmv'] - $previous['gmv'])
                    / $previous['gmv']
                ) * 100;

                $growthScore = round(
                    min(max(50 + $growthPercent, 0), 100),
                    2
                );
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
     * Calculate score berdasarkan selected date range
     * untuk seluruh affiliate.
     *
     * Berbeda dengan scoreBatch():
     * - scoreBatch() = snapshot harian
     * - scoreRange() = aggregate berdasarkan date range
     */
   public function scoreRange(
        Collection $currentPerformances,
        Collection $previousPerformances
    ): Collection {

        /*
        * ============================================================
        * NORMALIZE COLLECTION
        * ============================================================
        *
        * Bisa menerima:
        * - flat Collection
        * - Collection yang sudah groupBy affiliate_id
        */

        $currentPerformances = $this->flattenPerformanceRows(
            $currentPerformances
        );

        $previousPerformances = $this->flattenPerformanceRows(
            $previousPerformances
        );

        if ($currentPerformances->isEmpty()) {
            return collect();
        }

        /*
        * ============================================================
        * GROUP ONCE
        * ============================================================
        */

        $currentGrouped = $currentPerformances->groupBy(
            'affiliate_id'
        );

        $previousGrouped = $previousPerformances->groupBy(
            'affiliate_id'
        );

        /*
        * ============================================================
        * AGGREGATE CURRENT RANGE
        * ============================================================
        */

        $currentByAffiliate = $currentGrouped->map(
            fn (Collection $rows) =>
                $this->aggregateRangePerformance($rows)
        );

        /*
        * ============================================================
        * AGGREGATE PREVIOUS RANGE
        * ============================================================
        */

        $previousByAffiliate = $previousGrouped->map(
            fn (Collection $rows) =>
                $this->aggregateRangePerformance($rows)
        );

        /*
        * ============================================================
        * PERCENTILE
        * ============================================================
        *
        * Percentile dihitung berdasarkan aggregate seluruh
        * affiliate dalam selected range.
        */

        $percentiles = [
            'gmv' => $this->buildPercentileMap(
                $currentByAffiliate->pluck('gmv')
            ),

            'orders' => $this->buildPercentileMap(
                $currentByAffiliate->pluck('orders')
            ),

            'buyers' => $this->buildPercentileMap(
                $currentByAffiliate->pluck('buyers')
            ),

            'content' => $this->buildPercentileMap(
                $currentByAffiliate->pluck('content')
            ),

            'ctr' => $this->buildPercentileMap(
                $currentByAffiliate->pluck('ctr')
            ),

            'ctor' => $this->buildPercentileMap(
                $currentByAffiliate->pluck('ctor')
            ),
        ];

        /*
        * ============================================================
        * BUILD SCORE
        * ============================================================
        */

        return $currentByAffiliate->map(
            function (
                array $target,
                $affiliateId
            ) use (
                $currentGrouped,
                $previousByAffiliate,
                $percentiles
            ) {

                /*
                * Ambil rows affiliate langsung dari hasil groupBy.
                *
                * TIDAK melakukan:
                * $currentPerformances->where(...)
                */

                $rows = $currentGrouped->get(
                    $affiliateId,
                    collect()
                );

                /*
                * ====================================================
                * PERFORMANCE SCORE
                * ====================================================
                */

                $gmvScore = $percentiles['gmv'][
                    $this->normalizeKey($target['gmv'])
                ] ?? 0;

                $ordersScore = $percentiles['orders'][
                    $this->normalizeKey($target['orders'])
                ] ?? 0;

                $buyersScore = $percentiles['buyers'][
                    $this->normalizeKey($target['buyers'])
                ] ?? 0;

                $contentScore = $percentiles['content'][
                    $this->normalizeKey($target['content'])
                ] ?? 0;

                $ctrScore = $percentiles['ctr'][
                    $this->normalizeKey($target['ctr'])
                ] ?? 0;

                $ctorScore = $percentiles['ctor'][
                    $this->normalizeKey($target['ctor'])
                ] ?? 0;

                $engagementScore =
                    ($ctrScore * 0.50) +
                    ($ctorScore * 0.50);

                $performanceScore = round(
                    ($gmvScore * 0.40) +
                    ($ordersScore * 0.25) +
                    ($buyersScore * 0.15) +
                    ($contentScore * 0.10) +
                    ($engagementScore * 0.10),
                    2
                );

                /*
                * ====================================================
                * GROWTH SCORE
                * ====================================================
                */

                $growthScore = 0;
                $growthPercent = null;

                $previous = $previousByAffiliate->get(
                    $affiliateId
                );

                if (
                    $previous !== null &&
                    $previous['gmv'] > 0
                ) {

                    $growthPercent =
                        (
                            ($target['gmv'] - $previous['gmv'])
                            / $previous['gmv']
                        ) * 100;

                    /*
                    * -50% = 0
                    *   0% = 50
                    * +50% = 100
                    */

                    $growthScore = min(
                        max(50 + $growthPercent, 0),
                        100
                    );

                    $growthScore = round(
                        $growthScore,
                        2
                    );
                }

                /*
                * ====================================================
                * CONSISTENCY SCORE
                * ====================================================
                *
                * Menggunakan GMV harian dalam selected range.
                */

                $dailyGmvs = $rows
                    ->pluck('gmv')
                    ->map(
                        fn ($value) => (float) $value
                    )
                    ->filter(
                        fn ($value) => $value > 0
                    )
                    ->values();

                $consistencyScore = 0;

                if ($dailyGmvs->count() >= 2) {

                    $average = $dailyGmvs->avg();

                    if ($average > 0) {

                        $variance = $dailyGmvs
                            ->map(
                                fn ($value) =>
                                    pow(
                                        $value - $average,
                                        2
                                    )
                            )
                            ->avg();

                        $standardDeviation = sqrt(
                            $variance
                        );

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

                /*
                * ====================================================
                * OPPORTUNITY SCORE
                * ====================================================
                */

                $opportunityScore = $performanceScore;

                if ($previous) {
                    if ($dailyGmvs->count() >= 2) {
                        // Semua komponen tersedia
                        $opportunityScore = round(
                            ($performanceScore * 0.50) +
                            ($growthScore * 0.30) +
                            ($consistencyScore * 0.20),
                            2
                        );
                    } else {
                        // Hanya performance + growth yang tersedia.
                        // Bobot dinormalisasi dari 80% menjadi 100%.
                        $opportunityScore = round(
                            (
                                ($performanceScore * 0.50) +
                                ($growthScore * 0.30)
                            ) / 0.80,
                            2
                        );
                    }
                }

                /*
                * ====================================================
                * ACTION
                * ====================================================
                */

                $action = $this->determineAction(
                    $opportunityScore
                );

                /*
                * ====================================================
                * INSIGHTS
                * ====================================================
                */

                $insights = $this->generateRangeInsights(
                    $performanceScore,
                    $growthScore,
                    $consistencyScore,
                    $opportunityScore,
                    $growthPercent,
                    $previous !== null,
                    $dailyGmvs->count()
                );

                return [
                    'performance_score' => $performanceScore,
                    'growth_score' => $growthScore,
                    'consistency_score' => $consistencyScore,
                    'opportunity_score' => $opportunityScore,
                    'overall_score' => $opportunityScore,
                    'action' => $action,
                    'growth_percent' => $growthPercent,
                    'period_count' => $dailyGmvs->count(),
                    'insights' => $insights,
                ];
            }
        );
    }

    private function flattenPerformanceRows(
        Collection $collection
    ): Collection {

        $result = collect();

        foreach ($collection as $item) {

            if ($item instanceof Collection) {
                foreach ($item as $row) {
                    if ($row instanceof AffiliatePerformance) {
                        $result->push($row);
                    }
                }

                continue;
            }

            if ($item instanceof AffiliatePerformance) {
                $result->push($item);
            }
        }

        return $result->values();
    }

    /**
     * Aggregate beberapa snapshot menjadi satu range.
     */
    private function aggregateRangePerformance(
        Collection $rows
    ): array {
        $sum = function (string $field) use ($rows): float {
            return $rows->sum(
                fn ($row) => (float) ($row->{$field} ?? 0)
            );
        };

        $gmv = $sum('gmv');
        $orders = $sum('attributed_orders');
        $buyers = $sum('buyers');

        $impressions = $sum('impressions');
        $videoViews = $sum('video_views');

        /*
        * CTR weighted berdasarkan impressions.
        */
        $weightedCtr = $rows->sum(function ($row) {
            return
                (float) ($row->ctr ?? 0) *
                (float) ($row->impressions ?? 0);
        });

        $ctr = $impressions > 0
            ? $weightedCtr / $impressions
            : 0;

        /*
        * CTOR weighted berdasarkan video views.
        */
        $weightedCtor = $rows->sum(function ($row) {
            return
                (float) ($row->ctor ?? 0) *
                (float) ($row->video_views ?? 0);
        });

        $ctor = $videoViews > 0
            ? $weightedCtor / $videoViews
            : 0;

        /*
        * Content activity.
        */
        $content = $rows->sum(function ($row) {
            return
                (int) ($row->video_count ?? 0) +
                (int) ($row->live_count ?? 0);
        });

        return [
            'gmv' => $gmv,
            'orders' => $orders,
            'buyers' => $buyers,
            'content' => $content,
            'ctr' => $ctr,
            'ctor' => $ctor,
        ];
    }

    private function generateRangeInsights(
        float $performanceScore,
        float $growthScore,
        float $consistencyScore,
        float $opportunityScore,
        ?float $growthPercent,
        bool $hasPreviousPeriod,
        int $periodCount
    ): array {
        $insights = [];

        /*
        * Performance
        */
        if ($performanceScore >= 75) {
            $insights[] =
                'Performance berada di level tinggi dibandingkan affiliate lain pada periode ini.';
        } elseif ($performanceScore >= 50) {
            $insights[] =
                'Performance berada di level menengah dan masih memiliki ruang untuk ditingkatkan.';
        } else {
            $insights[] =
                'Performance masih relatif rendah dibandingkan affiliate lain pada periode ini.';
        }

        /*
        * Consistency
        */
        if ($periodCount >= 2) {
            if ($consistencyScore >= 75) {
                $insights[] =
                    'Performa GMV relatif konsisten sepanjang periode yang dipilih.';
            } elseif ($consistencyScore >= 50) {
                $insights[] =
                    'Performa cukup konsisten, tetapi masih terdapat fluktuasi GMV.';
            } else {
                $insights[] =
                    'Performa GMV cukup fluktuatif sepanjang periode yang dipilih.';
            }
        }

        /*
        * Growth
        */
        if ($hasPreviousPeriod && $growthPercent !== null) {
            if ($growthPercent > 0) {
                $insights[] = sprintf(
                    'GMV meningkat %.2f%% dibandingkan periode sebelumnya.',
                    $growthPercent
                );
            } elseif ($growthPercent < 0) {
                $insights[] = sprintf(
                    'GMV menurun %.2f%% dibandingkan periode sebelumnya.',
                    abs($growthPercent)
                );
            } else {
                $insights[] =
                    'GMV relatif stabil dibandingkan periode sebelumnya.';
            }
        }

        /*
        * Opportunity
        */
        if ($opportunityScore >= 75) {
            $insights[] =
                'Affiliate menunjukkan kombinasi performance, growth, dan consistency yang kuat pada periode ini.';
        } elseif ($opportunityScore < 35) {
            $insights[] =
                'Affiliate perlu dipantau karena kombinasi score pada periode ini masih rendah.';
        }

        return $insights;
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