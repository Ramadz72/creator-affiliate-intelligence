<?php

namespace App\Services;

use App\Models\Creator;

class CreatorAnalysisService
{
    public function analyze(Creator $creator): array
    {
        /*
        |--------------------------------------------------------------------------
        | Ambil maksimal 7 konten terbaru
        |--------------------------------------------------------------------------
        */

        $contents = $creator->contents()
            ->latest('content_date')
            ->latest('id')
            ->limit(7)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 1. PERFORMANCE SCORE
        |--------------------------------------------------------------------------
        */

        if ($contents->isEmpty()) {
            $performanceScore = 0;
            $averageViews = 0;
            $consistencyScore = 0;
        } else {
            $averageViews = $contents->avg('views');
            $maxViews = $contents->max('views');

            /*
            | Consistency:
            | semakin dekat rata-rata views dengan views tertinggi,
            | semakin konsisten performanya.
            */

            $consistencyScore = $maxViews > 0
                ? ($averageViews / $maxViews) * 100
                : 0;

            /*
            | View-to-follower ratio.
            |
            | 100% view/follower dianggap sebagai nilai maksimal
            | untuk komponen ini.
            */

            $viewRate = $creator->followers > 0
                ? ($averageViews / $creator->followers) * 100
                : 0;

            $viewPerformanceScore = min(100, $viewRate);

            /*
            | Performance:
            | 70% kemampuan menghasilkan views
            | 30% konsistensi
            */

            $performanceScore =
                ($viewPerformanceScore * 0.70) +
                ($consistencyScore * 0.30);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. ENGAGEMENT SCORE
        |--------------------------------------------------------------------------
        */

        if ($contents->isEmpty()) {
            $engagementScore = 0;
            $engagementRate = 0;
        } else {
            $totalViews = $contents->sum('views');

            $totalInteractions =
                $contents->sum('likes') +
                $contents->sum('comments') +
                $contents->sum('shares');

            $engagementRate = $totalViews > 0
                ? ($totalInteractions / $totalViews) * 100
                : 0;

            /*
            | 10% engagement rate = 100 score.
            |
            | Ini adalah benchmark internal V1,
            | bukan klaim benchmark industri.
            */

            $engagementScore = min(
                100,
                ($engagementRate / 10) * 100
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 3. AUDIENCE FIT SCORE
        |--------------------------------------------------------------------------
        |
        | Belum ada target audience brand.
        | 50 = neutral / belum dinilai.
        |
        */

        $audienceFitScore = 50;

        /*
        |--------------------------------------------------------------------------
        | 4. HISTORICAL SCORE
        |--------------------------------------------------------------------------
        */

        $campaignPerformances = $creator->campaigns()
            ->with('performances')
            ->get()
            ->pluck('performances')
            ->flatten()
            ->filter();

        if ($campaignPerformances->isEmpty()) {
            // Belum pernah punya campaign dengan actual performance.
            // Gunakan nilai netral.
            $historicalScore = 50;
            $averageRoas = null;
        } else {
            // Field database masih bernama "roi",
            // tetapi nilainya sekarang kita gunakan sebagai ROAS.
            $averageRoas = $campaignPerformances->avg('roi');

            /*
            * Mapping ROAS → Historical Score
            *
            * 0x  = 0
            * 1x  = 33.33
            * 2x  = 66.67
            * 3x  = 100
            *
            * ROAS >= 3x dibatasi 100.
            */
            $historicalScore = min(
                100,
                max(
                    0,
                    ($averageRoas / 3) * 100
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 5. DEAL VALUE SCORE
        |--------------------------------------------------------------------------
        */
            
        $rateCard = $creator->rateCards()
            ->orderBy('price')
            ->first();
        
        if (!$rateCard || $contents->isEmpty()) {
            $dealValueScore = 0;
            $averageCostPerView = null;
        } else {
            $averageViews = max($contents->avg('views'), 1);
            $price = max((float) $rateCard->price, 1);

            /*
            | Cost per view.
            */

            $averageCostPerView = $price / $averageViews;

            /*
            | Untuk TEST V1:
            | semakin rendah CPV relatif terhadap performa,
            | semakin tinggi deal value.
            |
            | Threshold internal sementara:
            | Rp100 per 1.000 views = score 100.
            */

            $costPerThousandViews =
                $averageCostPerView * 1000;

            $dealValueScore = min(
                100,
                (100 / max($costPerThousandViews, 0.01)) * 100
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 6. OVERALL SCORE
        |--------------------------------------------------------------------------
        |
        | Audience Fit belum dimasukkan karena belum ada
        | target audience sebagai pembanding.
        |
        | Bobot sementara:
        | Performance 50%
        | Engagement 30%
        | Historical 10%
        | Deal Value 10%
        |
        */

        $overallScore =
            ($performanceScore * 0.50) +
            ($engagementScore * 0.30) +
            ($historicalScore * 0.10) +
            ($dealValueScore * 0.10);

        $overallScore = round(
            min(100, max(0, $overallScore)),
            2
        );

        /*
        |--------------------------------------------------------------------------
        | 7. INSIGHTS / REASONING
        |--------------------------------------------------------------------------
        */

        $insights = [];

        /*
        | Performance
        */

        if ($performanceScore >= 80) {
            $insights[] = [
                'type' => 'positive',
                'title' => 'Performance creator kuat',
                'message' => sprintf(
                    'Berdasarkan %d content terbaru, creator memiliki rata-rata %s views dengan performance score %.2f.',
                    $contents->count(),
                    number_format($averageViews, 0, ',', '.'),
                    $performanceScore
                ),
            ];
        } elseif ($performanceScore >= 65) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Performance creator cukup baik',
                'message' => sprintf(
                    'Berdasarkan %d content terbaru, creator memiliki rata-rata %s views dengan performance score %.2f.',
                    $contents->count(),
                    number_format($averageViews, 0, ',', '.'),
                    $performanceScore
                ),
            ];
        } else {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Performance creator perlu diperhatikan',
                'message' => sprintf(
                    'Berdasarkan %d content terbaru, performance score creator berada di %.2f.',
                    $contents->count(),
                    $performanceScore
                ),
            ];
        }

        /*
        | Engagement
        */

        if ($engagementScore >= 70) {
            $insights[] = [
                'type' => 'positive',
                'title' => 'Engagement cukup kuat',
                'message' => sprintf(
                    'Rata-rata engagement rate creator berada di %.2f%% dengan engagement score %.2f.',
                    $engagementRate,
                    $engagementScore
                ),
            ];
        } elseif ($engagementScore >= 40) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Engagement masih perlu dipantau',
                'message' => sprintf(
                    'Engagement rate creator berada di %.2f%% dengan engagement score %.2f.',
                    $engagementRate,
                    $engagementScore
                ),
            ];
        } else {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Engagement relatif rendah',
                'message' => sprintf(
                    'Engagement rate creator berada di %.2f%% dengan engagement score %.2f.',
                    $engagementRate,
                    $engagementScore
                ),
            ];
        }

        /*
        | Historical
        */

        if ($campaignPerformances->isEmpty()) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Belum ada historical campaign',
                'message' => 'Creator belum memiliki data actual campaign performance. Historical score menggunakan nilai netral 50.',
            ];
        } elseif ($historicalScore >= 70) {
            $insights[] = [
                'type' => 'positive',
                'title' => 'Historical performance baik',
                'message' => sprintf(
                    'Terdapat %d performance campaign dengan rata-rata ROAS %.2fx. Data historical memberikan kontribusi positif terhadap penilaian creator.',
                    $campaignPerformances->count(),
                    $averageRoas
                ),
            ];
        } else {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Historical performance perlu diperhatikan',
                'message' => sprintf(
                    'Terdapat %d performance campaign dengan rata-rata ROAS %.2fx. Data historical belum memberikan score yang tinggi pada analisis saat ini.',
                    $campaignPerformances->count(),
                    $averageRoas
                ),
            ];
        }

        /*
        | Audience Fit
        */

        $insights[] = [
            'type' => 'info',
            'title' => 'Audience fit belum dievaluasi',
            'message' => 'Target audience brand belum tersedia sehingga audience fit belum dapat dibandingkan secara aktual.',
        ];

        /*
        | Deal Value
        */

        if ($dealValueScore >= 80) {
            $insights[] = [
                'type' => 'positive',
                'title' => 'Deal value menarik',
                'message' => sprintf(
                    'Rate card yang dianalisis memberikan deal value score %.2f dengan estimasi cost per view sebesar Rp%s.',
                    $dealValueScore,
                    number_format($averageCostPerView ?? 0, 2, ',', '.')
                ),
            ];
        } elseif ($dealValueScore >= 50) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Deal value perlu dipertimbangkan',
                'message' => sprintf(
                    'Rate card yang dianalisis memiliki deal value score %.2f dengan estimasi cost per view sebesar Rp%s.',
                    $dealValueScore,
                    number_format($averageCostPerView ?? 0, 2, ',', '.')
                ),
            ];
        } else {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Deal value perlu dinegosiasikan',
                'message' => sprintf(
                    'Rate card yang dianalisis memiliki deal value score %.2f dengan estimasi cost per view sebesar Rp%s.',
                    $dealValueScore,
                    number_format($averageCostPerView ?? 0, 2, ',', '.')
                ),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 8. RECOMMENDATION
        |--------------------------------------------------------------------------
        */

        if ($overallScore >= 80) {
            $recommendation = 'highly_recommended';
        } elseif ($overallScore >= 65) {
            $recommendation = 'recommended';
        } elseif ($overallScore >= 50) {
            $recommendation = 'negotiate';
        } else {
            $recommendation = 'not_recommended';
        }

        /*
        |--------------------------------------------------------------------------
        | RETURN TEST RESULT
        |--------------------------------------------------------------------------
        |
        | Belum ada CreatorScore::create().
        | Jadi database creator_scores TIDAK berubah.
        |
        */

       return [
            'creator_id' => $creator->id,
            'creator_name' => $creator->name,

            'period_start' => $contents->isNotEmpty()
                ? $contents->min('content_date')->toDateString()
                : null,

            'period_end' => $contents->isNotEmpty()
                ? $contents->max('content_date')->toDateString()
                : null,

            'content_count' => $contents->count(),
            'average_views' => round($averageViews ?? 0, 2),

            'engagement_rate' => round(
                $engagementRate ?? 0,
                2
            ),

            'performance_score' => round(
                $performanceScore,
                2
            ),

            'engagement_score' => round(
                $engagementScore,
                2
            ),

            'audience_fit_score' => $audienceFitScore,

            'historical_score' => round(
                $historicalScore,
                2
            ),

            'average_roas' => $averageRoas !== null
                ? round($averageRoas, 2)
                : null,

            'deal_value_score' => round(
                $dealValueScore,
                2
            ),

            'overall_score' => $overallScore,

            'recommendation' => $recommendation,

            'insights' => $insights,

            'rate_card' => $rateCard ? [
                'platform' => $rateCard->platform,
                'deliverable' => $rateCard->deliverable,
                'price' => (float) $rateCard->price,
            ] : null,

            'cost_per_view' => $averageCostPerView,
        ];
    }
}