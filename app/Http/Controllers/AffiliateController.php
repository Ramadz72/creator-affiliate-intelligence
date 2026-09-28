<?php

namespace App\Http\Controllers;

use App\Models\AffiliatePerformance;
use App\Models\ImportBatch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AffiliateController extends Controller
{
    public function index(Request $request): Response
    {
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
        $sort = (string) $request->input('sort', 'opportunity');
        $direction = strtolower((string) $request->input('direction', 'desc'));
        $action = trim((string) $request->input('action', ''));

        /*
        |--------------------------------------------------------------------------
        | Date Range
        |--------------------------------------------------------------------------
        |
        | Prioritas:
        | 1. start_date + end_date
        | 2. batch_id lama
        | 3. latest snapshot
        |
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

        if ($startDate && $endDate && $startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
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

        if (
            !$startDate ||
            !$endDate
        ) {
            return Inertia::render('affiliates/Index', [
                'affiliates' => [
                    'data' => [],
                    'total' => 0,
                    'per_page' => 20,
                    'current_page' => 1,
                    'last_page' => 1,
                ],

                'latest_period' => $latestBatch
                    ? [
                        'start' => $latestBatch->period_start?->format('Y-m-d'),
                        'end' => $latestBatch->period_end?->format('Y-m-d'),
                    ]
                    : null,

                'selected_period' => null,
                'comparison_period' => null,

                'latest_batch_id' => $latestBatch?->id,
                'selected_batch_id' => null,
                'previous_batch_id' => null,
                'previous_period' => null,

                'import_batches' => $importBatches,

                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'action' => $action,
            ]);
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
            return Inertia::render('affiliates/Index', [
                'affiliates' => [
                    'data' => [],
                    'total' => 0,
                    'per_page' => 20,
                    'current_page' => 1,
                    'last_page' => 1,
                ],

                'latest_period' => $latestBatch
                    ? [
                        'start' => $latestBatch->period_start?->format('Y-m-d'),
                        'end' => $latestBatch->period_end?->format('Y-m-d'),
                    ]
                    : null,

                'selected_period' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                ],

                'comparison_period' => [
                    'start' => $comparisonStart->format('Y-m-d'),
                    'end' => $comparisonEnd->format('Y-m-d'),
                ],

                'latest_batch_id' => $latestBatch?->id,
                'selected_batch_id' => null,
                'previous_batch_id' => null,

                'previous_period' => [
                    'start' => $comparisonStart->format('Y-m-d'),
                    'end' => $comparisonEnd->format('Y-m-d'),
                ],

                'import_batches' => $importBatches,

                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'action' => $action,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Current Performance
        |--------------------------------------------------------------------------
        */

        $currentPerformances = AffiliatePerformance::query()
            ->with([
                'affiliate:id,name,username,platform,status',
            ])
            ->whereIn(
                'import_batch_id',
                $currentBatches->pluck('id')
            )
            ->whereHas('affiliate', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->get()
            ->groupBy('affiliate_id');

        /*
        |--------------------------------------------------------------------------
        | Comparison Performance
        |--------------------------------------------------------------------------
        */

        $comparisonPerformances = AffiliatePerformance::query()
            ->whereIn(
                'import_batch_id',
                $comparisonBatches->pluck('id')
            )
            ->get()
            ->groupBy('affiliate_id');

        /*
        |--------------------------------------------------------------------------
        | Latest Score Per Affiliate
        |--------------------------------------------------------------------------
        |
        | Score menggunakan snapshot terakhir yang tersedia
        | di dalam periode yang sedang dipilih.
        |
        */

        $scoreRows = DB::table('affiliate_scores')
            ->join(
                'import_batches',
                'affiliate_scores.import_batch_id',
                '=',
                'import_batches.id'
            )
            ->where('import_batches.status', 'completed')
            ->where('import_batches.uploaded_by', Auth::id())
            ->whereIn(
                'affiliate_scores.import_batch_id',
                $currentBatches->pluck('id')
            )
            ->orderByDesc('import_batches.period_start')
            ->orderByDesc('affiliate_scores.id')
            ->get([
                'affiliate_scores.affiliate_id',
                'affiliate_scores.performance_score',
                'affiliate_scores.opportunity_score',
                'affiliate_scores.action',
                'import_batches.period_start',
                'import_batches.id as batch_id',
            ])
            ->groupBy('affiliate_id')
            ->map(function ($rows) {
                return $rows->first();
            });

        /*
        |--------------------------------------------------------------------------
        | Build Affiliate Data
        |--------------------------------------------------------------------------
        */

        $affiliateRows = $currentPerformances
            ->map(function ($rows, $affiliateId) use (
                $comparisonPerformances,
                $scoreRows,
                $startDate,
                $endDate
            ) {
                $first = $rows->first();

                /*
                | Current Period
                */

                $currentGmv = (float) $rows->sum('gmv');

                $currentOrders = (int) $rows->sum(
                    'attributed_orders'
                );

                $currentProductsSold = (int) $rows->sum(
                    'products_sold'
                );

                $currentAov = $currentOrders > 0
                    ? $currentGmv / $currentOrders
                    : 0;

                $currentCtr = (float) $rows->avg('ctr');
                $currentCtor = (float) $rows->avg('ctor');

                /*
                | Previous Period
                */

                $previousRows = $comparisonPerformances
                    ->get($affiliateId);

                $previousGmv = $previousRows
                    ? (float) $previousRows->sum('gmv')
                    : null;

                /*
                | Movement
                */

                $changePercent = null;

                if (
                    $previousGmv !== null &&
                    $previousGmv > 0
                ) {
                    $changePercent =
                        (
                            ($currentGmv - $previousGmv)
                            / $previousGmv
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
                | Score
                */

                $score = $scoreRows->get($affiliateId);

                return [
                    'id' => $first->affiliate?->id,

                    'name' => $first->affiliate?->name ?? '-',

                    'username' => $first->affiliate?->username ?? '-',

                    'platform' => $first->affiliate?->platform ?? 'TikTok',

                    'status' => $first->affiliate?->status ?? 'active',

                    'score' => [
                        'performance' => $score?->performance_score !== null
                            ? (float) $score->performance_score
                            : null,

                        'opportunity' => $score?->opportunity_score !== null
                            ? (float) $score->opportunity_score
                            : null,

                        'action' => $score?->action ?? 'MONITOR',
                    ],

                    'latest_performance' => [
                        'gmv' => $currentGmv,

                        'attributed_orders' => $currentOrders,

                        'products_sold' => $currentProductsSold,

                        'aov' => $currentAov,

                        'ctr' => $currentCtr,

                        'ctor' => $currentCtor,

                        'period_start' => $startDate->format('Y-m-d'),

                        'period_end' => $endDate->format('Y-m-d'),
                    ],

                    'movement' => [
                        'previous_gmv' => $previousGmv,

                        'gmv_change' => $previousGmv !== null
                            ? $currentGmv - $previousGmv
                            : null,

                        'gmv_change_percent' => $changePercent,

                        'status' => $movement,
                    ],
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $searchLower = strtolower($search);

            $affiliateRows = $affiliateRows
                ->filter(function ($affiliate) use ($searchLower) {
                    return str_contains(
                        strtolower($affiliate['name']),
                        $searchLower
                    ) ||
                    str_contains(
                        strtolower($affiliate['username']),
                        $searchLower
                    );
                })
                ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | Action Filter
        |--------------------------------------------------------------------------
        */

        if ($action !== '') {
            $affiliateRows = $affiliateRows
                ->filter(function ($affiliate) use ($action) {
                    return $affiliate['score']['action'] === $action;
                })
                ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        switch ($sort) {
            case 'growth':

                $affiliateRows = $affiliateRows
                    ->sortByDesc(function ($affiliate) {
                        return $affiliate['movement']['gmv_change_percent']
                            ?? -INF;
                    })
                    ->values();

                break;

            case 'decline':

                $affiliateRows = $affiliateRows
                    ->sortBy(function ($affiliate) {
                        return $affiliate['movement']['gmv_change_percent']
                            ?? INF;
                    })
                    ->values();

                break;

            case 'gmv':

                $affiliateRows = $affiliateRows
                    ->sortBy(
                        fn ($affiliate) =>
                            $affiliate['latest_performance']['gmv'],
                        SORT_NUMERIC,
                        $direction === 'desc'
                    )
                    ->values();

                break;

            case 'orders':

                $affiliateRows = $affiliateRows
                    ->sortBy(
                        fn ($affiliate) =>
                            $affiliate['latest_performance']['attributed_orders'],
                        SORT_NUMERIC,
                        $direction === 'desc'
                    )
                    ->values();

                break;

            case 'aov':

                $affiliateRows = $affiliateRows
                    ->sortBy(
                        fn ($affiliate) =>
                            $affiliate['latest_performance']['aov'],
                        SORT_NUMERIC,
                        $direction === 'desc'
                    )
                    ->values();

                break;

            case 'ctr':

                $affiliateRows = $affiliateRows
                    ->sortBy(
                        fn ($affiliate) =>
                            $affiliate['latest_performance']['ctr'],
                        SORT_NUMERIC,
                        $direction === 'desc'
                    )
                    ->values();

                break;

            case 'ctor':

                $affiliateRows = $affiliateRows
                    ->sortBy(
                        fn ($affiliate) =>
                            $affiliate['latest_performance']['ctor'],
                        SORT_NUMERIC,
                        $direction === 'desc'
                    )
                    ->values();

                break;

            case 'performance':

                $affiliateRows = $affiliateRows
                    ->sortBy(
                        fn ($affiliate) =>
                            $affiliate['score']['performance']
                                ?? -INF,
                        SORT_NUMERIC,
                        $direction === 'desc'
                    )
                    ->values();

                break;

            case 'opportunity':

                $affiliateRows = $affiliateRows
                    ->sortBy(
                        fn ($affiliate) =>
                            $affiliate['score']['opportunity']
                                ?? -INF,
                        SORT_NUMERIC,
                        $direction === 'desc'
                    )
                    ->values();

                break;

            case 'name':

                $affiliateRows = $affiliateRows
                    ->sortBy(
                        fn ($affiliate) =>
                            strtolower($affiliate['name']),
                        SORT_NATURAL,
                        $direction === 'desc'
                    )
                    ->values();

                break;

            default:
                break;
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = 20;

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $total = $affiliateRows->count();

        $paginatedItems = $affiliateRows
            ->slice(
                ($currentPage - 1) * $perPage,
                $perPage
            )
            ->values();

        $performances = new LengthAwarePaginator(
            $paginatedItems,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Selected / Previous Batch
        |--------------------------------------------------------------------------
        */

        $selectedBatch = $currentBatches->last();

        $previousBatch = $comparisonBatches->last();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render('affiliates/Index', [
            'affiliates' => $performances,

            'latest_period' => $latestBatch
                ? [
                    'start' => $latestBatch->period_start?->format('Y-m-d'),
                    'end' => $latestBatch->period_end?->format('Y-m-d'),
                ]
                : null,

            'selected_period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],

            'comparison_period' => [
                'start' => $comparisonStart->format('Y-m-d'),
                'end' => $comparisonEnd->format('Y-m-d'),
            ],

            'latest_batch_id' => $latestBatch?->id,

            'selected_batch_id' => $selectedBatch?->id,

            'previous_batch_id' => $previousBatch?->id,

            'previous_period' => [
                'start' => $comparisonStart->format('Y-m-d'),
                'end' => $comparisonEnd->format('Y-m-d'),
            ],

            'import_batches' => $importBatches,

            'search' => $search,

            'sort' => $sort,

            'direction' => $direction,

            'action' => $action,
        ]);
    }
}