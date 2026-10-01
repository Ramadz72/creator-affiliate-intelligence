<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Campaign;
use App\Models\Creator;
use App\Models\CreatorScore;
use App\Models\ImportBatch;
use App\Services\AffiliateScoreService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Latest Completed Batch
        |--------------------------------------------------------------------------
        */

        $latestBatch = ImportBatch::query()
            ->where('status', 'completed')
            ->where('uploaded_by', $user->id)
            ->whereNotNull('period_start')
            ->whereNotNull('period_end')
            ->orderByDesc('period_start')
            ->orderByDesc('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Selected Date Range
        |--------------------------------------------------------------------------
        */

        $startDate = null;
        $endDate = null;

        /*
        |--------------------------------------------------------------------------
        | 1. Prioritas utama → tanggal dari request
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('start_date') &&
            $request->filled('end_date')
        ) {
            try {
                $startDate = Carbon::parse(
                    $request->input('start_date')
                )->startOfDay();

                $endDate = Carbon::parse(
                    $request->input('end_date')
                )->startOfDay();
            } catch (\Throwable $e) {
                $startDate = null;
                $endDate = null;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Kalau tidak ada request → ambil dari session
        |--------------------------------------------------------------------------
        */

        if (!$startDate || !$endDate) {
            $savedStartDate = $request->session()->get('dashboard_start_date');
            $savedEndDate = $request->session()->get('dashboard_end_date');

            if ($savedStartDate && $savedEndDate) {
                try {
                    $startDate = Carbon::parse($savedStartDate)->startOfDay();
                    $endDate = Carbon::parse($savedEndDate)->startOfDay();
                } catch (\Throwable $e) {
                    $startDate = null;
                    $endDate = null;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Default → Latest Batch
        |--------------------------------------------------------------------------
        */

        if (!$startDate || !$endDate) {
            if ($latestBatch) {
                $startDate = Carbon::parse(
                    $latestBatch->period_start
                )->startOfDay();

                $endDate = Carbon::parse(
                    $latestBatch->period_end
                )->startOfDay();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        */

        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        /*
        |--------------------------------------------------------------------------
        | Save Dashboard Period
        |--------------------------------------------------------------------------
        |
        | Agar ketika user pindah menu lalu kembali ke Dashboard,
        | periode terakhir yang dipilih tetap digunakan.
        |
        */

        $request->session()->put([
            'dashboard_start_date' => $startDate->format('Y-m-d'),
            'dashboard_end_date' => $endDate->format('Y-m-d'),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Comparison Period
        |--------------------------------------------------------------------------
        |
        | 01 Sep - 27 Sep
        |       ↓
        | 05 Aug - 31 Aug
        |
        */

        $periodDays = $startDate->diffInDays($endDate) + 1;

        $previousEndDate = $startDate
            ->copy()
            ->subDay();

        $previousStartDate = $previousEndDate
            ->copy()
            ->subDays($periodDays - 1);

        /*
        |--------------------------------------------------------------------------
        | Current Batches
        |--------------------------------------------------------------------------
        */

        $currentBatches = ImportBatch::query()
            ->where('status', 'completed')
            ->where('uploaded_by', $user->id)
            ->whereNotNull('period_start')
            ->whereNotNull('period_end')
            ->whereDate('period_start', '>=', $startDate)
            ->whereDate('period_end', '<=', $endDate)
            ->orderBy('period_start')
            ->orderBy('id')
            ->get([
                'id',
                'file_name',
                'period_start',
                'period_end',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Comparison Batches
        |--------------------------------------------------------------------------
        |
        | Comparison boleh kosong.
        | Jangan membuat request gagal hanya karena tidak ada data sebelumnya.
        |
        */

        $comparisonBatches = ImportBatch::query()
            ->where('status', 'completed')
            ->where('uploaded_by', $user->id)
            ->whereNotNull('period_start')
            ->whereNotNull('period_end')
            ->whereDate('period_start', '>=', $previousStartDate)
            ->whereDate('period_end', '<=', $previousEndDate)
            ->orderBy('period_start')
            ->orderBy('id')
            ->get([
                'id',
                'file_name',
                'period_start',
                'period_end',
            ]);

        $currentBatchIds = $currentBatches
            ->pluck('id')
            ->values();

        $comparisonBatchIds = $comparisonBatches
            ->pluck('id')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Basic Counts
        |--------------------------------------------------------------------------
        */

        $totalCreators = Creator::query()
            ->where('user_id', $user->id)
            ->count();

        $totalAffiliates = Affiliate::query()
            ->where('user_id', $user->id)
            ->count();

        $activeCampaigns = Campaign::query()
            ->whereHas('creator', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('status', 'Running')
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Current Affiliate Performance
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | Jangan hydrate Eloquent AffiliatePerformance.
        | Gunakan DB::table() supaya memory tetap kecil.
        |
        */

        $currentPerformances = collect();

        if ($currentBatchIds->isNotEmpty()) {
            $currentPerformances = DB::table('affiliate_performances')
                ->join(
                    'affiliates',
                    'affiliates.id',
                    '=',
                    'affiliate_performances.affiliate_id'
                )
                ->whereIn(
                    'affiliate_performances.import_batch_id',
                    $currentBatchIds
                )
                ->where('affiliates.user_id', $user->id)
                ->select([
                    'affiliate_performances.affiliate_id',
                    'affiliate_performances.import_batch_id',
                    'affiliate_performances.gmv',
                    'affiliate_performances.attributed_orders',
                    'affiliate_performances.buyers',
                    'affiliate_performances.products_sold',
                    'affiliate_performances.impressions',
                    'affiliate_performances.video_views',
                    'affiliate_performances.ctr',
                    'affiliate_performances.ctor',
                    'affiliate_performances.video_count',
                    'affiliate_performances.live_count',
                ])
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Comparison Affiliate Performance
        |--------------------------------------------------------------------------
        */

        $comparisonPerformances = collect();

        if ($comparisonBatchIds->isNotEmpty()) {
            $comparisonPerformances = DB::table('affiliate_performances')
                ->join(
                    'affiliates',
                    'affiliates.id',
                    '=',
                    'affiliate_performances.affiliate_id'
                )
                ->whereIn(
                    'affiliate_performances.import_batch_id',
                    $comparisonBatchIds
                )
                ->where('affiliates.user_id', $user->id)
                ->select([
                    'affiliate_performances.affiliate_id',
                    'affiliate_performances.import_batch_id',
                    'affiliate_performances.gmv',
                    'affiliate_performances.attributed_orders',
                    'affiliate_performances.buyers',
                    'affiliate_performances.products_sold',
                    'affiliate_performances.impressions',
                    'affiliate_performances.video_views',
                    'affiliate_performances.ctr',
                    'affiliate_performances.ctor',
                    'affiliate_performances.video_count',
                    'affiliate_performances.live_count',
                ])
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Aggregate Current Metrics
        |--------------------------------------------------------------------------
        */

        $totalGmv = (float) $currentPerformances->sum(
            fn ($row) => (float) $row->gmv
        );

        $totalOrders = (int) $currentPerformances->sum(
            fn ($row) => (int) $row->attributed_orders
        );

        /*
        |--------------------------------------------------------------------------
        | Previous Metrics
        |--------------------------------------------------------------------------
        */

        $previousGmv = (float) $comparisonPerformances->sum(
            fn ($row) => (float) $row->gmv
        );

        $previousOrders = (int) $comparisonPerformances->sum(
            fn ($row) => (int) $row->attributed_orders
        );

        /*
        |--------------------------------------------------------------------------
        | Growth
        |--------------------------------------------------------------------------
        |
        | Jika comparison kosong → null.
        |
        */

        $gmvGrowth = $previousGmv > 0
            ? (($totalGmv - $previousGmv) / $previousGmv) * 100
            : null;

        $ordersGrowth = $previousOrders > 0
            ? (($totalOrders - $previousOrders) / $previousOrders) * 100
            : null;

        /*
        |--------------------------------------------------------------------------
        | Affiliate Range Score
        |--------------------------------------------------------------------------
        |
        | Ini sekarang menjadi sumber utama:
        |
        | - Performance
        | - Growth
        | - Consistency
        | - Opportunity
        | - Action
        |
        | Bukan lagi AffiliateScore harian terakhir.
        |
        */

        $scoreService = app(AffiliateScoreService::class);

        $rangeScores = $scoreService->scoreRange(
            $currentPerformances,
            $comparisonPerformances
        );

        /*
        |--------------------------------------------------------------------------
        | Average Creator Score
        |--------------------------------------------------------------------------
        |
        | Creator masih menggunakan score terakhir dalam selected period.
        |
        */

        $creatorLatestIds = CreatorScore::query()
            ->whereHas('creator', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereDate('period_start', '>=', $startDate)
            ->whereDate('period_end', '<=', $endDate)
            ->selectRaw('MAX(id) as id')
            ->groupBy('creator_id')
            ->pluck('id');

        $avgCreatorScore = null;

        if ($creatorLatestIds->isNotEmpty()) {
            $avgCreatorScore = CreatorScore::query()
                ->whereIn('id', $creatorLatestIds)
                ->avg('overall_score');
        }

        /*
        |--------------------------------------------------------------------------
        | Average Affiliate Opportunity
        |--------------------------------------------------------------------------
        */

        $avgAffiliateOpportunity = $rangeScores->isNotEmpty()
            ? $rangeScores->avg('opportunity_score')
            : null;

        /*
        |--------------------------------------------------------------------------
        | Affiliate Action Center
        |--------------------------------------------------------------------------
        */

        $affiliateActionCounts = [
            'CHASE' => 0,
            'SUPPORT' => 0,
            'MONITOR' => 0,
            'DEPRIORITIZE' => 0,
        ];

        foreach ($rangeScores as $score) {
            $action = $score['action'] ?? null;

            if ($action && array_key_exists($action, $affiliateActionCounts)) {
                $affiliateActionCounts[$action]++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Creator Action Center
        |--------------------------------------------------------------------------
        */

        $creatorReviewCount = 0;

        if ($creatorLatestIds->isNotEmpty()) {
            $creatorReviewCount = CreatorScore::query()
                ->whereIn('id', $creatorLatestIds)
                ->whereIn('recommendation', [
                    'negotiate',
                    'not_recommended',
                ])
                ->count();
        }

        $actionRequired = [
            'creators_to_review' => $creatorReviewCount,
            'affiliates_to_support' => $affiliateActionCounts['SUPPORT'],
            'need_monitoring' => $affiliateActionCounts['MONITOR'],
            'deprioritize' => $affiliateActionCounts['DEPRIORITIZE'],
        ];

        /*
        |--------------------------------------------------------------------------
        | Affiliate GMV By Affiliate
        |--------------------------------------------------------------------------
        |
        | Digunakan untuk Overview + Insight.
        |
        */

        $affiliateGmv = $currentPerformances
            ->groupBy('affiliate_id')
            ->map(function ($rows) {
                return [
                    'gmv' => (float) $rows->sum(
                        fn ($row) => (float) $row->gmv
                    ),
                    'orders' => (int) $rows->sum(
                        fn ($row) => (int) $row->attributed_orders
                    ),
                    'products_sold' => (int) $rows->sum(
                        fn ($row) => (int) $row->products_sold
                    ),
                    'video_views' => (int) $rows->sum(
                        fn ($row) => (int) $row->video_views
                    ),
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Dashboard Attention & Opportunity
        |--------------------------------------------------------------------------
        |
        | Menggunakan rangeScores yang sama dengan Affiliate Index & Insights.
        | Tidak menjalankan scoreRange() ulang.
        |
        */

        $monitoringCount = 0;
        $potentialCount = 0;

        foreach ($rangeScores as $affiliateId => $score) {
            $performance = $affiliateGmv->get($affiliateId);

            if (!$performance) {
                continue;
            }

            $gmv = (float) ($performance['gmv'] ?? 0);

            $consistencyScore = $score['consistency_score'] ?? null;
            $opportunityScore = $score['opportunity_score'] ?? null;

            /*
            | Attention / Monitoring
            |
            | Sama dengan Insights:
            | GMV >= 5 juta
            | Consistency < 40
            */

            if (
                $gmv >= 5_000_000
                && $consistencyScore !== null
                && $consistencyScore < 40
            ) {
                $monitoringCount++;
            }

            /*
            | Potential Opportunity
            |
            | Sama dengan Insights:
            | GMV < 5 juta
            | Opportunity Score >= 70
            */

            if (
                $gmv < 5_000_000
                && $opportunityScore !== null
                && $opportunityScore >= 70
            ) {
                $potentialCount++;
            }
        }
        /*
        |--------------------------------------------------------------------------
        | Affiliate IDs Needed For UI
        |--------------------------------------------------------------------------
        */

        $overviewAffiliateIds = $rangeScores
            ->sortByDesc('opportunity_score')
            ->take(2)
            ->keys();

        $topGmvAffiliateIds = $affiliateGmv
            ->sortByDesc('gmv')
            ->take(3)
            ->keys();

        $neededAffiliateIds = $overviewAffiliateIds
            ->merge($topGmvAffiliateIds)
            ->unique()
            ->values();

        $affiliateMeta = collect();

        if ($neededAffiliateIds->isNotEmpty()) {
            $affiliateMeta = Affiliate::query()
                ->where('user_id', $user->id)
                ->whereIn('id', $neededAffiliateIds)
                ->get([
                    'id',
                    'name',
                    'username',
                    'platform',
                ])
                ->keyBy('id');
        }

        /*
        |--------------------------------------------------------------------------
        | Affiliate Overview
        |--------------------------------------------------------------------------
        */

        $affiliateOverview = $rangeScores
            ->sortByDesc('opportunity_score')
            ->take(2)
            ->map(function ($score, $affiliateId) use (
                $affiliateMeta,
                $affiliateGmv
            ) {
                $affiliate = $affiliateMeta->get($affiliateId);
                $performance = $affiliateGmv->get($affiliateId);

                return [
                    'id' => (int) $affiliateId,
                    'name' => $affiliate?->name,
                    'username' => $affiliate?->username,
                    'platform' => $affiliate?->platform,
                    'score' => (float) ($score['opportunity_score'] ?? 0),
                    'action' => $score['action'] ?? null,
                    'gmv' => (float) ($performance['gmv'] ?? 0),
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Creator Overview
        |--------------------------------------------------------------------------
        */

        $creatorOverview = collect();

        if ($creatorLatestIds->isNotEmpty()) {
            $creatorOverview = CreatorScore::query()
                ->whereIn('id', $creatorLatestIds)
                ->whereHas('creator', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with('creator:id,name,platform,category')
                ->orderByDesc('overall_score')
                ->limit(2)
                ->get()
                ->map(function ($score) {
                    return [
                        'id' => $score->creator_id,
                        'name' => $score->creator?->name,
                        'platform' => $score->creator?->platform,
                        'category' => $score->creator?->category,
                        'score' => (float) $score->overall_score,
                    ];
                })
                ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | Affiliate Performance Chart
        |--------------------------------------------------------------------------
        |
        | Maksimal 7 batch/snapshot terakhir dalam selected range.
        |
        */

        $affiliatePerformance = collect();

        if ($currentBatchIds->isNotEmpty()) {
            $affiliatePerformance = DB::table('affiliate_performances')
                ->join(
                    'import_batches',
                    'import_batches.id',
                    '=',
                    'affiliate_performances.import_batch_id'
                )
                ->join(
                    'affiliates',
                    'affiliates.id',
                    '=',
                    'affiliate_performances.affiliate_id'
                )
                ->whereIn(
                    'affiliate_performances.import_batch_id',
                    $currentBatchIds
                )
                ->where('affiliates.user_id', $user->id)
                ->select([
                    'affiliate_performances.import_batch_id as batch_id',
                    'import_batches.period_start',
                    'import_batches.period_end',
                ])
                ->selectRaw('SUM(affiliate_performances.gmv) as gmv')
                ->selectRaw('SUM(affiliate_performances.attributed_orders) as orders')
                ->groupBy(
                    'affiliate_performances.import_batch_id',
                    'import_batches.period_start',
                    'import_batches.period_end'
                )
                ->orderByDesc('affiliate_performances.import_batch_id')
                ->limit(7)
                ->get()
                ->sortBy('batch_id')
                ->values()
                ->map(function ($row) {
                    return [
                        'batch_id' => (int) $row->batch_id,
                        'period_start' => $row->period_start
                            ? Carbon::parse($row->period_start)->format('Y-m-d')
                            : null,
                        'period_end' => $row->period_end
                            ? Carbon::parse($row->period_end)->format('Y-m-d')
                            : null,
                        'gmv' => (float) $row->gmv,
                        'orders' => (int) $row->orders,
                    ];
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Dashboard Insight Preview
        |--------------------------------------------------------------------------
        |
        | Dibuat dari data range yang sama.
        | Tidak lagi mengambil latest batch.
        |
        */

        $dashboardInsight = null;

        if ($currentPerformances->isNotEmpty()) {
            $topGmv = $affiliateGmv
                ->sortByDesc('gmv')
                ->take(3)
                ->map(function ($performance, $affiliateId) use (
                    $affiliateMeta,
                    $rangeScores
                ) {
                    $affiliate = $affiliateMeta->get($affiliateId);
                    $score = $rangeScores->get($affiliateId);

                    return [
                        'affiliate_id' => (int) $affiliateId,
                        'name' => $affiliate?->name,
                        'username' => $affiliate?->username,
                        'gmv' => (float) $performance['gmv'],
                        'orders' => (int) $performance['orders'],
                        'products_sold' => (int) $performance['products_sold'],
                        'video_views' => (int) $performance['video_views'],
                        'performance_score' => $score
                            ? (float) ($score['performance_score'] ?? 0)
                            : null,
                        'growth_score' => $score
                            ? (float) ($score['growth_score'] ?? 0)
                            : null,
                        'consistency_score' => $score
                            ? (float) ($score['consistency_score'] ?? 0)
                            : null,
                        'opportunity_score' => $score
                            ? (float) ($score['opportunity_score'] ?? 0)
                            : null,
                        'overall_score' => $score
                            ? (float) ($score['overall_score'] ?? 0)
                            : null,
                        'action' => $score['action'] ?? null,
                    ];
                })
                ->values()
                ->all();

            $insights = [];

            /*
            |--------------------------------------------------------------
            | Insight 1 — Performance Movement
            |--------------------------------------------------------------
            */

            if ($gmvGrowth !== null) {
                $movementTitle = $gmvGrowth >= 0
                    ? 'GMV mengalami pertumbuhan'
                    : 'GMV mengalami penurunan';

                $movementHeadline = sprintf(
                    '%s%0.1f%% dibanding periode sebelumnya.',
                    $gmvGrowth >= 0 ? '+' : '',
                    $gmvGrowth
                );

                $movementDescription = sprintf(
                    'GMV periode %s – %s tercatat Rp%s.',
                    $startDate->format('d M Y'),
                    $endDate->format('d M Y'),
                    number_format($totalGmv, 0, ',', '.')
                );

                $movementAction = $gmvGrowth >= 0
                    ? 'Pertahankan pola konten dan affiliate yang memberikan kontribusi GMV.'
                    : 'Periksa affiliate dan konten dengan penurunan GMV untuk menentukan tindak lanjut.';

                $insights[] = [
                    'type' => 'movement',
                    'title' => $movementTitle,
                    'headline' => $movementHeadline,
                    'description' => $movementDescription,
                    'recommended_action' => $movementAction,
                ];
            } else {
                $insights[] = [
                    'type' => 'movement',
                    'title' => 'Belum ada data pembanding',
                    'headline' => 'Growth belum dapat dihitung.',
                    'description' => sprintf(
                        'Periode %s – %s memiliki data performa, tetapi periode pembanding %s – %s belum memiliki data.',
                        $startDate->format('d M Y'),
                        $endDate->format('d M Y'),
                        $previousStartDate->format('d M Y'),
                        $previousEndDate->format('d M Y')
                    ),
                    'recommended_action' => 'Import snapshot pada periode sebelumnya jika ingin melihat perbandingan growth.',
                ];
            }

            /*
            |--------------------------------------------------------------
            | Insight 2 — Top Performer
            |--------------------------------------------------------------
            */

            if (!empty($topGmv)) {
                $top = $topGmv[0];

                $insights[] = [
                    'type' => 'top_performer',
                    'title' => 'Top GMV',
                    'headline' => $top['name'] ?? $top['username'] ?? 'Affiliate',
                    'description' => sprintf(
                        'Menghasilkan GMV Rp%s dengan %s orders pada periode terpilih.',
                        number_format($top['gmv'], 0, ',', '.'),
                        number_format($top['orders'], 0, ',', '.')
                    ),
                    'recommended_action' => 'Pertahankan dukungan dan evaluasi pola konten yang menghasilkan performa tersebut.',
                    'affiliate_id' => $top['affiliate_id'],
                ];
            }

            /*
            |--------------------------------------------------------------
            | Insight 3 — Action Distribution
            |--------------------------------------------------------------
            */

            $totalScoredAffiliates = $rangeScores->count();

            if ($totalScoredAffiliates > 0) {
                $insights[] = [
                    'type' => 'action',
                    'title' => 'Affiliate Action Center',
                    'headline' => sprintf(
                        '%d affiliate dianalisis',
                        $totalScoredAffiliates
                    ),
                    'description' => sprintf(
                        'Terdapat %d SUPPORT, %d MONITOR, dan %d DEPRIORITIZE pada periode terpilih.',
                        $affiliateActionCounts['SUPPORT'],
                        $affiliateActionCounts['MONITOR'],
                        $affiliateActionCounts['DEPRIORITIZE']
                    ),
                    'recommended_action' => 'Gunakan action category sebagai dasar prioritas follow-up affiliate.',
                ];
            }

            /*
            |--------------------------------------------------------------
            | Insight 4 — Attention / Monitoring
            |--------------------------------------------------------------
            */

            $insights[] = [
                'type' => 'monitoring',
                'title' => 'High GMV, Low Consistency',
                'headline' => $monitoringCount > 0
                    ? sprintf(
                        '%d affiliate memiliki GMV tinggi tetapi consistency rendah.',
                        $monitoringCount
                    )
                    : 'Tidak ada pola yang perlu diperhatikan.',
                'description' => $monitoringCount > 0
                    ? 'Kondisi ini menunjukkan performa yang perlu dipantau agar kontribusi GMV tetap berkelanjutan.'
                    : 'Tidak ditemukan affiliate dengan GMV tinggi dan consistency score rendah pada periode terpilih.',
                'recommended_action' => $monitoringCount > 0
                    ? 'Pantau konsistensi konten dan performa pada periode berikutnya.'
                    : 'Lanjutkan pemantauan performa pada periode berikutnya.',
                'count' => $monitoringCount,
            ];

            /*
            |--------------------------------------------------------------
            | Insight 5 — Potential Opportunity
            |--------------------------------------------------------------
            */

            $insights[] = [
                'type' => 'potential',
                'title' => 'Potential Opportunity',
                'headline' => $potentialCount > 0
                    ? sprintf(
                        '%d affiliate memiliki opportunity score tinggi dengan GMV di bawah Rp5 juta.',
                        $potentialCount
                    )
                    : 'Belum ada potential opportunity.',
                'description' => $potentialCount > 0
                    ? 'Affiliate dalam kategori ini menunjukkan sinyal yang layak diperhatikan meskipun kontribusi GMV masih relatif kecil.'
                    : 'Belum ditemukan affiliate dengan opportunity score tinggi dan GMV di bawah Rp5 juta pada periode terpilih.',
                'recommended_action' => $potentialCount > 0
                    ? 'Pertimbangkan pengembangan, aktivasi konten, atau dukungan tambahan.'
                    : 'Lanjutkan pemantauan dan evaluasi performa affiliate.',
                'count' => $potentialCount,
            ];

            /*
            |--------------------------------------------------------------
            | Summary
            |--------------------------------------------------------------
            */

            $totalProductsSold = (int) $currentPerformances->sum(
                fn ($row) => (int) $row->products_sold
            );

            $dashboardInsight = [
                'period' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                ],

                'summary' => [
                    'total_gmv' => $totalGmv,
                    'total_orders' => $totalOrders,
                    'total_products_sold' => $totalProductsSold,
                    'affiliate_count' => $rangeScores->count(),
                ],

                'insights' => $insights,

                'top_gmv' => $topGmv,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Dashboard', [
            'stats' => [
                'creators' => $totalCreators,
                'affiliates' => $totalAffiliates,
                'campaigns' => $activeCampaigns,

                'gmv' => $totalGmv,
                'orders' => $totalOrders,

                'avg_creator_score' => $avgCreatorScore !== null
                    ? round((float) $avgCreatorScore, 1)
                    : null,

                'avg_affiliate_opportunity' => $avgAffiliateOpportunity !== null
                    ? round((float) $avgAffiliateOpportunity, 1)
                    : null,

                'gmv_growth' => $gmvGrowth !== null
                    ? round($gmvGrowth, 1)
                    : null,

                'orders_growth' => $ordersGrowth !== null
                    ? round($ordersGrowth, 1)
                    : null,
            ],

            /*
            |--------------------------------------------------------------------------
            | Selected Period
            |--------------------------------------------------------------------------
            */

            'selected_period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Comparison Period
            |--------------------------------------------------------------------------
            */

            'comparison_period' => [
                'start' => $previousStartDate->format('Y-m-d'),
                'end' => $previousEndDate->format('Y-m-d'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Chart
            |--------------------------------------------------------------------------
            */

            'affiliate_performance' => $affiliatePerformance,

            /*
            |--------------------------------------------------------------------------
            | Action Center
            |--------------------------------------------------------------------------
            */

            'action_required' => $actionRequired,

            /*
            |--------------------------------------------------------------------------
            | Overview
            |--------------------------------------------------------------------------
            */

            'creator_overview' => $creatorOverview,

            'affiliate_overview' => $affiliateOverview,

            /*
            |--------------------------------------------------------------------------
            | Insight
            |--------------------------------------------------------------------------
            */

            'insight_preview' => $dashboardInsight
                ? [
                    'period' => $dashboardInsight['period'],
                    'summary' => $dashboardInsight['summary'],
                    'insights' => $dashboardInsight['insights'],
                    'top_gmv' => array_slice(
                        $dashboardInsight['top_gmv'],
                        0,
                        3
                    ),
                ]
                : null,
        ]);
    }
}