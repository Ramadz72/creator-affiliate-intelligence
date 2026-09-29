<?php

namespace App\Http\Controllers;

use App\Models\AffiliatePerformance;
use App\Models\ImportBatch;
use Carbon\Carbon;
use App\Services\AffiliateScoreService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class AffiliateController extends Controller
{
    public function index(Request $request): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Benchmark Start
        |--------------------------------------------------------------------------
        */

        $debugStart = microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Latest Snapshot
        |--------------------------------------------------------------------------
        */

        $latestBatch = ImportBatch::query()
            ->where('status', 'completed')
            ->where('uploaded_by', Auth::id())
            ->orderByDesc('period_start')
            ->orderByDesc('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $search = trim((string) $request->input('search', ''));

        $sort = (string) $request->input(
            'sort',
            'opportunity'
        );

        $direction = strtolower(
            (string) $request->input(
                'direction',
                'desc'
            )
        );

        $action = trim(
            (string) $request->input(
                'action',
                ''
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Date Range
        |--------------------------------------------------------------------------
        */

        $startDate = null;
        $endDate = null;

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
        | Backward Compatibility: batch_id
        |--------------------------------------------------------------------------
        */

        if (!$startDate || !$endDate) {
            $batchId = $request->input('batch_id');

            if ($batchId) {
                $legacyBatch = ImportBatch::query()
                    ->where('status', 'completed')
                    ->where('uploaded_by', Auth::id())
                    ->where('id', $batchId)
                    ->first();

                if ($legacyBatch) {
                    $startDate = Carbon::parse(
                        $legacyBatch->period_start
                    )->startOfDay();

                    $endDate = Carbon::parse(
                        $legacyBatch->period_end
                    )->startOfDay();
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Default: Latest Snapshot
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
        | Normalize Date Range
        |--------------------------------------------------------------------------
        */

        if (
            $startDate &&
            $endDate &&
            $startDate->gt($endDate)
        ) {
            [$startDate, $endDate] = [
                $endDate,
                $startDate,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Comparison Period
        |--------------------------------------------------------------------------
        |
        | Contoh:
        |
        | 02 Sep - 02 Sep
        | => 01 Sep - 01 Sep
        |
        | 02 Sep - 03 Sep
        | => 31 Agu - 01 Sep
        |
        | 01 Sep - 10 Sep
        | => 22 Agu - 31 Agu
        |
        */

        $comparisonStart = null;
        $comparisonEnd = null;

        if ($startDate && $endDate) {
            $days = $startDate->diffInDays($endDate) + 1;

            $comparisonEnd = $startDate
                ->copy()
                ->subDay();

            $comparisonStart = $comparisonEnd
                ->copy()
                ->subDays($days - 1);
        }

        /*
        |--------------------------------------------------------------------------
        | Allowed Sorts
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'opportunity',
            'performance',
            'gmv',
            'orders',
            'aov',
            'ctr',
            'ctor',
            'name',
            'growth',
            'decline',
        ];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'opportunity';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        /*
        |--------------------------------------------------------------------------
        | Allowed Actions
        |--------------------------------------------------------------------------
        */

        $allowedActions = [
            'CHASE',
            'SUPPORT',
            'MONITOR',
            'DEPRIORITIZE',
        ];

        if (!in_array($action, $allowedActions, true)) {
            $action = '';
        }

        /*
        |--------------------------------------------------------------------------
        | Available Import Batches
        |--------------------------------------------------------------------------
        */

        $importBatches = ImportBatch::query()
            ->where('status', 'completed')
            ->where('uploaded_by', Auth::id())
            ->orderByDesc('period_start')
            ->orderByDesc('id')
            ->get([
                'id',
                'file_name',
                'period_start',
                'period_end',
            ]);

        /*
        |--------------------------------------------------------------------------
        | No Date / No Data
        |--------------------------------------------------------------------------
        */

        if (!$startDate || !$endDate) {
            return Inertia::render(
                'affiliates/Index',
                [
                    'affiliates' => [
                        'data' => [],
                        'total' => 0,
                        'per_page' => 20,
                        'current_page' => 1,
                        'last_page' => 1,
                    ],

                    'latest_period' => $latestBatch
                        ? [
                            'start' =>
                                $latestBatch
                                    ->period_start
                                    ?->format('Y-m-d'),

                            'end' =>
                                $latestBatch
                                    ->period_end
                                    ?->format('Y-m-d'),
                        ]
                        : null,

                    'selected_period' => null,

                    'comparison_period' => null,

                    'latest_batch_id' =>
                        $latestBatch?->id,

                    'selected_batch_id' => null,

                    'previous_batch_id' => null,

                    'previous_period' => null,

                    'import_batches' => $importBatches,

                    'search' => $search,

                    'sort' => $sort,

                    'direction' => $direction,

                    'action' => $action,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Current Batches
        |--------------------------------------------------------------------------
        */

        $currentBatches = ImportBatch::query()
            ->where('status', 'completed')
            ->where('uploaded_by', Auth::id())
            ->whereBetween('period_start', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->orderBy('period_start')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Comparison Batches
        |--------------------------------------------------------------------------
        */

        $comparisonBatches = ImportBatch::query()
            ->where('status', 'completed')
            ->where('uploaded_by', Auth::id())
            ->whereBetween('period_start', [
                $comparisonStart->toDateString(),
                $comparisonEnd->toDateString(),
            ])
            ->orderBy('period_start')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | No Current Data
        |--------------------------------------------------------------------------
        */

        if ($currentBatches->isEmpty()) {
            return Inertia::render(
                'affiliates/Index',
                [
                    'affiliates' => [
                        'data' => [],
                        'total' => 0,
                        'per_page' => 20,
                        'current_page' => 1,
                        'last_page' => 1,
                    ],

                    'latest_period' => $latestBatch
                        ? [
                            'start' =>
                                $latestBatch
                                    ->period_start
                                    ?->format('Y-m-d'),

                            'end' =>
                                $latestBatch
                                    ->period_end
                                    ?->format('Y-m-d'),
                        ]
                        : null,

                    'selected_period' => [
                        'start' =>
                            $startDate->format('Y-m-d'),

                        'end' =>
                            $endDate->format('Y-m-d'),
                    ],

                    'comparison_period' => [
                        'start' =>
                            $comparisonStart->format('Y-m-d'),

                        'end' =>
                            $comparisonEnd->format('Y-m-d'),
                    ],

                    'latest_batch_id' =>
                        $latestBatch?->id,

                    'selected_batch_id' => null,

                    'previous_batch_id' => null,

                    'previous_period' => [
                        'start' =>
                            $comparisonStart->format('Y-m-d'),

                        'end' =>
                            $comparisonEnd->format('Y-m-d'),
                    ],

                    'import_batches' => $importBatches,

                    'search' => $search,

                    'sort' => $sort,

                    'direction' => $direction,

                    'action' => $action,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Current Performance Query
        |--------------------------------------------------------------------------
        */

        $currentPerformanceRows = DB::table('affiliate_performances')
            ->join(
                'affiliates',
                'affiliates.id',
                '=',
                'affiliate_performances.affiliate_id'
            )
            ->whereIn(
                'affiliate_performances.import_batch_id',
                $currentBatches->pluck('id')
            )
            ->where(
                'affiliates.user_id',
                Auth::id()
            )
            ->select([
                'affiliate_performances.affiliate_id',

                'affiliates.name',
                'affiliates.username',
                'affiliates.platform',
                'affiliates.status',

                DB::raw('SUM(affiliate_performances.gmv) as gmv'),

                DB::raw(
                    'SUM(affiliate_performances.attributed_orders) as orders'
                ),

                DB::raw(
                    'SUM(affiliate_performances.products_sold) as products_sold'
                ),

                DB::raw(
                    'SUM(affiliate_performances.impressions) as impressions'
                ),

                DB::raw(
                    'SUM(affiliate_performances.video_views) as video_views'
                ),

                DB::raw(
                    'SUM(
                        affiliate_performances.ctr *
                        affiliate_performances.impressions
                    ) as ctr_weighted'
                ),

                DB::raw(
                    'SUM(
                        affiliate_performances.ctor *
                        affiliate_performances.video_views
                    ) as ctor_weighted'
                ),

                DB::raw(
                    'COUNT(*) as period_count'
                ),
            ])
            ->groupBy([
                'affiliate_performances.affiliate_id',
                'affiliates.name',
                'affiliates.username',
                'affiliates.platform',
                'affiliates.status',
            ])
            ->get();

        $debugAfterCurrentQuery = microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Aggregate Current Performance
        |--------------------------------------------------------------------------
        */

        $currentAggregates = [];

        foreach ($currentPerformanceRows as $row) {
            $affiliateId = (int) $row->affiliate_id;

            $impressions = (float) ($row->impressions ?? 0);
            $videoViews = (float) ($row->video_views ?? 0);

            $ctr = $impressions > 0
                ? (float) ($row->ctr_weighted ?? 0) / $impressions
                : 0;

            $ctor = $videoViews > 0
                ? (float) ($row->ctor_weighted ?? 0) / $videoViews
                : 0;

            $currentAggregates[$affiliateId] = [
                'affiliate' => [
                    'id' => $affiliateId,
                    'name' => $row->name,
                    'username' => $row->username,
                    'platform' => $row->platform,
                    'status' => $row->status,
                ],

                'affiliate_id' => $affiliateId,

                'gmv' => (float) ($row->gmv ?? 0),

                'orders' => (int) ($row->orders ?? 0),

                'products_sold' =>
                    (int) ($row->products_sold ?? 0),

                'impressions' => $impressions,

                'video_views' => $videoViews,

                'ctr' => $ctr,

                'ctor' => $ctor,

                'period_count' =>
                    (int) ($row->period_count ?? 0),
            ];
        }

        $debugAfterCurrentAggregate = microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Comparison Performance Query
        |--------------------------------------------------------------------------
        */

        $comparisonPerformanceRows = DB::table(
            'affiliate_performances'
        )
            ->whereIn(
                'import_batch_id',
                $comparisonBatches->pluck('id')
            )
            ->select([
                'affiliate_id',
                DB::raw('SUM(gmv) as gmv'),
            ])
            ->groupBy('affiliate_id')
            ->get();

        $debugAfterComparisonQuery = microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Aggregate Comparison Performance
        |--------------------------------------------------------------------------
        */

        $comparisonAggregates = [];

        foreach ($comparisonPerformanceRows as $row) {
            $affiliateId = $row->affiliate_id;

            $comparisonAggregates[$affiliateId] = [
                'gmv' =>
                    (float) (
                        $row->gmv ?? 0
                    ),
            ];
        }

        $debugAfterComparisonAggregate = microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Range Scoring Data
        |--------------------------------------------------------------------------
        |
        | Query ini khusus untuk AffiliateScoreService.
        | Tidak menggunakan Eloquent model agar tetap ringan.
        |
        */

        $scoreService =
            app(AffiliateScoreService::class);

        $scoreCurrentStart =
            microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Current Period - Scoring Rows
        |--------------------------------------------------------------------------
        */

        $scoreCurrentPerformances = DB::table(
            'affiliate_performances'
        )
            ->join(
                'affiliates',
                'affiliates.id',
                '=',
                'affiliate_performances.affiliate_id'
            )
            ->whereIn(
                'affiliate_performances.import_batch_id',
                $currentBatches->pluck('id')
            )
            ->where(
                'affiliates.user_id',
                Auth::id()
            )
            ->select([
                'affiliate_performances.affiliate_id',
                'affiliate_performances.gmv',
                'affiliate_performances.attributed_orders',
                'affiliate_performances.buyers',
                'affiliate_performances.impressions',
                'affiliate_performances.video_views',
                'affiliate_performances.ctr',
                'affiliate_performances.ctor',
                'affiliate_performances.video_count',
                'affiliate_performances.live_count',
            ])
            ->get();

        $scoreCurrentQueryEnd =
            microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Comparison Period - Scoring Rows
        |--------------------------------------------------------------------------
        */

        $scoreComparisonPerformances = DB::table(
            'affiliate_performances'
        )
            ->join(
                'affiliates',
                'affiliates.id',
                '=',
                'affiliate_performances.affiliate_id'
            )
            ->whereIn(
                'affiliate_performances.import_batch_id',
                $comparisonBatches->pluck('id')
            )
            ->where(
                'affiliates.user_id',
                Auth::id()
            )
            ->select([
                'affiliate_performances.affiliate_id',
                'affiliate_performances.gmv',
                'affiliate_performances.attributed_orders',
                'affiliate_performances.buyers',
                'affiliate_performances.impressions',
                'affiliate_performances.video_views',
                'affiliate_performances.ctr',
                'affiliate_performances.ctor',
                'affiliate_performances.video_count',
                'affiliate_performances.live_count',
            ])
            ->get();

        $scoreComparisonQueryEnd =
            microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Calculate Range Scores
        |--------------------------------------------------------------------------
        */

        $rangeScores =
            $scoreService->scoreRange(
                $scoreCurrentPerformances,
                $scoreComparisonPerformances
            );

        $scoreRangeEnd =
            microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Build Affiliate Data
        |--------------------------------------------------------------------------
        */

        $affiliateRows = collect($currentAggregates)
        ->map(
            function (
                $aggregate,
                $affiliateId
            ) use (
                $comparisonAggregates,
                $rangeScores,
                $startDate,
                $endDate,
            ) {
                $affiliate = $aggregate['affiliate'];

                /*
                |--------------------------------------------------------------------------
                | Current Period
                |--------------------------------------------------------------------------
                */

                $currentGmv =
                    (float) ($aggregate['gmv'] ?? 0);

                $currentOrders =
                    (int) ($aggregate['orders'] ?? 0);

                $currentProductsSold =
                    (int) ($aggregate['products_sold'] ?? 0);

                $currentAov =
                    $currentOrders > 0
                        ? $currentGmv / $currentOrders
                        : 0;

                $currentCtr =
                    (float) ($aggregate['ctr'] ?? 0);

                $currentCtor =
                    (float) ($aggregate['ctor'] ?? 0);

                /*
                |--------------------------------------------------------------------------
                | Previous Period
                |--------------------------------------------------------------------------
                */

                $previousGmv =
                    $comparisonAggregates[$affiliateId]['gmv']
                    ?? null;

                /*
                |--------------------------------------------------------------------------
                | Movement
                |--------------------------------------------------------------------------
                */

                $changePercent = null;

                if (
                    $previousGmv !== null &&
                    $previousGmv > 0
                ) {
                    $changePercent =
                        (
                            (
                                $currentGmv -
                                $previousGmv
                            ) /
                            $previousGmv
                        ) * 100;
                }

                $movement = match (true) {
                    $changePercent === null
                        => 'NO_BASELINE',

                    $changePercent > 0
                        => 'UP',

                    $changePercent < 0
                        => 'DOWN',

                    default
                        => 'STABLE',
                };

                /*
                |--------------------------------------------------------------------------
                | Score
                |--------------------------------------------------------------------------
                */

                $score =
                    $rangeScores->get($affiliateId);

                /*
                |--------------------------------------------------------------------------
                | Return
                |--------------------------------------------------------------------------
                */

                return [
                    'id' =>
                        $affiliate['id']
                        ?? $affiliateId,

                    'name' =>
                        $affiliate['name']
                        ?? '-',

                    'username' =>
                        $affiliate['username']
                        ?? '-',

                    'platform' =>
                        $affiliate['platform']
                        ?? 'TikTok',

                    'status' =>
                        $affiliate['status']
                        ?? 'active',

                    'score' => [
                        'performance' =>
                            $score[
                                'performance_score'
                            ] ?? null,

                        'growth' =>
                            $score[
                                'growth_score'
                            ] ?? null,

                        'consistency' =>
                            $score[
                                'consistency_score'
                            ] ?? null,

                        'opportunity' =>
                            $score[
                                'opportunity_score'
                            ] ?? null,

                        'action' =>
                            $score[
                                'action'
                            ] ?? 'MONITOR',

                        'growth_percent' =>
                            $score[
                                'growth_percent'
                            ] ?? null,

                        'period_count' =>
                            $score[
                                'period_count'
                            ] ?? 0,

                        'insights' =>
                            $score[
                                'insights'
                            ] ?? [],
                    ],

                    'latest_performance' => [
                        'gmv' =>
                            $currentGmv,

                        'attributed_orders' =>
                            $currentOrders,

                        'products_sold' =>
                            $currentProductsSold,

                        'aov' =>
                            $currentAov,

                        'ctr' =>
                            $currentCtr,

                        'ctor' =>
                            $currentCtor,

                        'period_start' =>
                            $startDate->format('Y-m-d'),

                        'period_end' =>
                            $endDate->format('Y-m-d'),
                    ],

                    'movement' => [
                        'previous_gmv' =>
                            $previousGmv,

                        'gmv_change' =>
                            $previousGmv !== null
                                ? $currentGmv -
                                    $previousGmv
                                : null,

                        'gmv_change_percent' =>
                            $changePercent,

                        'status' =>
                            $movement,
                    ],
                ];
            }
        )
        ->values();

        $debugAfterBuild = microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $searchLower =
                strtolower($search);

            $affiliateRows =
                $affiliateRows
                    ->filter(
                        function ($affiliate) use (
                            $searchLower
                        ) {
                            return
                                str_contains(
                                    strtolower(
                                        $affiliate['name']
                                    ),
                                    $searchLower
                                ) ||
                                str_contains(
                                    strtolower(
                                        $affiliate['username']
                                    ),
                                    $searchLower
                                );
                        }
                    )
                    ->values();
        }

        $debugAfterSearch = microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Action Filter
        |--------------------------------------------------------------------------
        */

        if ($action !== '') {
            $affiliateRows =
                $affiliateRows
                    ->filter(
                        function ($affiliate) use (
                            $action
                        ) {
                            return
                                $affiliate['score']['action']
                                === $action;
                        }
                    )
                    ->values();
        }

        $debugAfterAction = microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        switch ($sort) {
            case 'growth':

                $affiliateRows =
                    $affiliateRows
                        ->sortByDesc(
                            function ($affiliate) {
                                return
                                    $affiliate[
                                        'movement'
                                    ][
                                        'gmv_change_percent'
                                    ] ?? -INF;
                            }
                        )
                        ->values();

                break;

            case 'decline':

                $affiliateRows =
                    $affiliateRows
                        ->sortBy(
                            function ($affiliate) {
                                return
                                    $affiliate[
                                        'movement'
                                    ][
                                        'gmv_change_percent'
                                    ] ?? INF;
                            }
                        )
                        ->values();

                break;

            case 'gmv':

                $affiliateRows =
                    $affiliateRows
                        ->sortBy(
                            fn ($affiliate) =>
                                $affiliate[
                                    'latest_performance'
                                ]['gmv'],
                            SORT_NUMERIC,
                            $direction === 'desc'
                        )
                        ->values();

                break;

            case 'orders':

                $affiliateRows =
                    $affiliateRows
                        ->sortBy(
                            fn ($affiliate) =>
                                $affiliate[
                                    'latest_performance'
                                ][
                                    'attributed_orders'
                                ],
                            SORT_NUMERIC,
                            $direction === 'desc'
                        )
                        ->values();

                break;

            case 'aov':

                $affiliateRows =
                    $affiliateRows
                        ->sortBy(
                            fn ($affiliate) =>
                                $affiliate[
                                    'latest_performance'
                                ]['aov'],
                            SORT_NUMERIC,
                            $direction === 'desc'
                        )
                        ->values();

                break;

            case 'ctr':

                $affiliateRows =
                    $affiliateRows
                        ->sortBy(
                            fn ($affiliate) =>
                                $affiliate[
                                    'latest_performance'
                                ]['ctr'],
                            SORT_NUMERIC,
                            $direction === 'desc'
                        )
                        ->values();

                break;

            case 'ctor':

                $affiliateRows =
                    $affiliateRows
                        ->sortBy(
                            fn ($affiliate) =>
                                $affiliate[
                                    'latest_performance'
                                ]['ctor'],
                            SORT_NUMERIC,
                            $direction === 'desc'
                        )
                        ->values();

                break;

            case 'performance':

                $affiliateRows =
                    $affiliateRows
                        ->sortBy(
                            fn ($affiliate) =>
                                $affiliate[
                                    'score'
                                ]['performance']
                                ?? -INF,
                            SORT_NUMERIC,
                            $direction === 'desc'
                        )
                        ->values();

                break;

            case 'opportunity':

                $affiliateRows =
                    $affiliateRows
                        ->sortBy(
                            fn ($affiliate) =>
                                $affiliate[
                                    'score'
                                ]['opportunity']
                                ?? -INF,
                            SORT_NUMERIC,
                            $direction === 'desc'
                        )
                        ->values();

                break;

            case 'name':

                $affiliateRows =
                    $affiliateRows
                        ->sortBy(
                            fn ($affiliate) =>
                                strtolower(
                                    $affiliate['name']
                                ),
                            SORT_NATURAL,
                            $direction === 'desc'
                        )
                        ->values();

                break;

            default:
                break;
        }

        $debugAfterSort = microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = 20;

        $currentPage =
            LengthAwarePaginator::resolveCurrentPage();

        $total =
            $affiliateRows->count();

        $paginatedItems =
            $affiliateRows
                ->slice(
                    ($currentPage - 1) *
                        $perPage,
                    $perPage
                )
                ->values();

        $performances =
            new LengthAwarePaginator(
                $paginatedItems,
                $total,
                $perPage,
                $currentPage,
                [
                    'path' =>
                        request()->url(),

                    'query' =>
                        request()->query(),
                ]
            );

        $debugAfterPagination =
            microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Selected / Previous Batch
        |--------------------------------------------------------------------------
        */

        $selectedBatch =
            $currentBatches->last();

        $previousBatch =
            $comparisonBatches->last();

     

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'affiliates/Index',
            [
                'affiliates' =>
                    $performances,

                'latest_period' =>
                    $latestBatch
                        ? [
                            'start' =>
                                $latestBatch
                                    ->period_start
                                    ?->format(
                                        'Y-m-d'
                                    ),

                            'end' =>
                                $latestBatch
                                    ->period_end
                                    ?->format(
                                        'Y-m-d'
                                    ),
                        ]
                        : null,

                'selected_period' => [
                    'start' =>
                        $startDate->format(
                            'Y-m-d'
                        ),

                    'end' =>
                        $endDate->format(
                            'Y-m-d'
                        ),
                ],

                'comparison_period' => [
                    'start' =>
                        $comparisonStart->format(
                            'Y-m-d'
                        ),

                    'end' =>
                        $comparisonEnd->format(
                            'Y-m-d'
                        ),
                ],

                'latest_batch_id' =>
                    $latestBatch?->id,

                'selected_batch_id' =>
                    $selectedBatch?->id,

                'previous_batch_id' =>
                    $previousBatch?->id,

                'previous_period' => [
                    'start' =>
                        $comparisonStart->format(
                            'Y-m-d'
                        ),

                    'end' =>
                        $comparisonEnd->format(
                            'Y-m-d'
                        ),
                ],

                'import_batches' =>
                    $importBatches,

                'search' =>
                    $search,

                'sort' =>
                    $sort,

                'direction' =>
                    $direction,

                'action' =>
                    $action,
            ]
        );
    }
}