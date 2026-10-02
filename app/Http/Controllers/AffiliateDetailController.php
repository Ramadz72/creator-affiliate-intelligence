<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\ImportBatch;
use App\Services\AffiliateScoreService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class AffiliateDetailController extends Controller
{
    public function show(
        Request $request,
        Affiliate $affiliate,
        AffiliateScoreService $scoreService
    ): Response {
        $requestStart = microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $affiliate->user_id === Auth::id(),
            404
        );

        $userId = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | Latest Available Date
        |--------------------------------------------------------------------------
        */

        $latestDate = ImportBatch::query()
            ->where('status', 'completed')
            ->where('uploaded_by', $userId)
            ->max('period_start');

        /*
        |--------------------------------------------------------------------------
        | Selected Period
        |--------------------------------------------------------------------------
        */

        $sessionKey = "affiliate_detail_period_{$affiliate->id}";

        $savedPeriod = $request->session()->get($sessionKey);

        $startInput = $request->input('start_date');
        $endInput = $request->input('end_date');

        if ($startInput && $endInput) {
            // URL/request punya prioritas tertinggi
            $startDate = Carbon::parse($startInput)->startOfDay();
            $endDate = Carbon::parse($endInput)->startOfDay();
        } elseif (
            is_array($savedPeriod)
            && !empty($savedPeriod['start'])
            && !empty($savedPeriod['end'])
        ) {
            // Gunakan periode terakhir yang dipilih
            $startDate = Carbon::parse(
                $savedPeriod['start']
            )->startOfDay();

            $endDate = Carbon::parse(
                $savedPeriod['end']
            )->startOfDay();
        } else {
            // Fallback ke tanggal terbaru
            $startDate = $latestDate
                ? Carbon::parse($latestDate)->startOfDay()
                : now()->startOfDay();

            $endDate = $startDate->copy();
        }

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [
                $endDate,
                $startDate,
            ];
        }

        $from = $request->input('from', 'affiliates');

        $backUrl = match ($from) {
            'dashboard' => route('dashboard', [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]),

            'insights' => url('/insights') . '?' . http_build_query([
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]),

            'affiliates' => route('affiliates.index', [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]),

            default => route('affiliates.index', [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]),
        };

        $request->session()->put(
            $sessionKey,
            [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Previous Period
        |--------------------------------------------------------------------------
        */

        $days = $startDate->diffInDays($endDate) + 1;

        $previousEndDate = $startDate
            ->copy()
            ->subDay();

        $previousStartDate = $previousEndDate
            ->copy()
            ->subDays($days - 1);

        /*
        |--------------------------------------------------------------------------
        | Current Batches
        |--------------------------------------------------------------------------
        */

        $currentBatchIds = ImportBatch::query()
            ->where('status', 'completed')
            ->where('uploaded_by', $userId)
            ->whereDate('period_start', '>=', $startDate)
            ->whereDate('period_end', '<=', $endDate)
            ->orderBy('period_start')
            ->pluck('id')
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Comparison Batches
        |--------------------------------------------------------------------------
        */

        $comparisonBatchIds = ImportBatch::query()
            ->where('status', 'completed')
            ->where('uploaded_by', $userId)
            ->whereDate('period_start', '>=', $previousStartDate)
            ->whereDate('period_end', '<=', $previousEndDate)
            ->orderBy('period_start')
            ->pluck('id')
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Affiliate Performance History
        |--------------------------------------------------------------------------
        |
        | Tetap mempertahankan seluruh field yang digunakan Show.vue.
        | Tetapi tidak lagi menggunakan Eloquent + relationship.
        |
        */

        $historyStart = microtime(true);

        $performances = DB::table('affiliate_performances as ap')
            ->join(
                'import_batches as ib',
                'ib.id',
                '=',
                'ap.import_batch_id'
            )
            ->where('ap.affiliate_id', $affiliate->id)
            ->where('ib.status', 'completed')
            ->where('ib.uploaded_by', $userId)
            ->select([
                'ap.id',
                'ap.affiliate_id',
                'ap.import_batch_id',

                'ap.gmv',
                'ap.gmv_live',
                'ap.gmv_video',
                'ap.gmv_product_card',
                'ap.refund',

                'ap.attributed_orders',
                'ap.products_sold',
                'ap.aov',

                'ap.ctr',
                'ap.ctor',

                'ap.impressions',
                'ap.video_views',
                'ap.buyers',

                'ap.commission',

                'ap.live_count',
                'ap.video_count',
                'ap.showcase_products',

                'ap.content_samples',
                'ap.samples_sent',
                'ap.products_returned',

                'ib.period_start',
                'ib.period_end',
            ])
            ->orderByDesc('ib.period_start')
            ->orderByDesc('ib.id')
            ->get();

        Log::info('AFFILIATE DETAIL PERFORMANCE', [
            'step' => 'history query',
            'seconds' => round(microtime(true) - $historyStart, 3),
            'rows' => $performances->count(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Current Range Performances
        |--------------------------------------------------------------------------
        |
        | Hanya affiliate yang sedang dibuka.
        | Digunakan untuk aggregate detail.
        |
        */

        $currentPerformances = $performances->filter(
            function ($performance) use ($startDate, $endDate) {
                if (!$performance->period_start) {
                    return false;
                }

                $date = Carbon::parse($performance->period_start);

                return $date->between(
                    $startDate,
                    $endDate
                );
            }
        )->values();

        /*
        |--------------------------------------------------------------------------
        | Previous Range Performances
        |--------------------------------------------------------------------------
        */

        $previousPerformances = $performances->filter(
            function ($performance) use (
                $previousStartDate,
                $previousEndDate
            ) {
                if (!$performance->period_start) {
                    return false;
                }

                $date = Carbon::parse($performance->period_start);

                return $date->between(
                    $previousStartDate,
                    $previousEndDate
                );
            }
        )->values();

        /*
        |--------------------------------------------------------------------------
        | Score Data
        |--------------------------------------------------------------------------
        |
        | Score tetap menggunakan seluruh affiliate.
        | Ini penting supaya percentile/detail = index.
        |
        */

        $scoreCurrentStart = microtime(true);

        $scoreCurrentPerformances = collect();

        if (!empty($currentBatchIds)) {
            $scoreCurrentPerformances = DB::table(
                'affiliate_performances as ap'
            )
                ->join(
                    'affiliates as a',
                    'a.id',
                    '=',
                    'ap.affiliate_id'
                )
                ->whereIn(
                    'ap.import_batch_id',
                    $currentBatchIds
                )
                ->where('a.user_id', $userId)
                ->select([
                    'ap.affiliate_id',
                    'ap.import_batch_id',
                    'ap.gmv',
                    'ap.attributed_orders',
                    'ap.buyers',
                    'ap.products_sold',
                    'ap.video_views',
                    'ap.impressions',
                    'ap.ctr',
                    'ap.ctor',
                    'ap.video_count',
                    'ap.live_count',
                ])
                ->get();
        }

        Log::info('AFFILIATE DETAIL PERFORMANCE', [
            'step' => 'score current query',
            'seconds' => round(
                microtime(true) - $scoreCurrentStart,
                3
            ),
            'rows' => $scoreCurrentPerformances->count(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Comparison Score Data
        |--------------------------------------------------------------------------
        */

        $scoreComparisonStart = microtime(true);

        $scoreComparisonPerformances = collect();

        if (!empty($comparisonBatchIds)) {
            $scoreComparisonPerformances = DB::table(
                'affiliate_performances as ap'
            )
                ->join(
                    'affiliates as a',
                    'a.id',
                    '=',
                    'ap.affiliate_id'
                )
                ->whereIn(
                    'ap.import_batch_id',
                    $comparisonBatchIds
                )
                ->where('a.user_id', $userId)
                ->select([
                    'ap.affiliate_id',
                    'ap.import_batch_id',
                    'ap.gmv',
                    'ap.attributed_orders',
                    'ap.buyers',
                    'ap.products_sold',
                    'ap.video_views',
                    'ap.impressions',
                    'ap.ctr',
                    'ap.ctor',
                    'ap.video_count',
                    'ap.live_count',
                ])
                ->get();
        }

        Log::info('AFFILIATE DETAIL PERFORMANCE', [
            'step' => 'score comparison query',
            'seconds' => round(
                microtime(true) - $scoreComparisonStart,
                3
            ),
            'rows' => $scoreComparisonPerformances->count(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Dynamic Range Score
        |--------------------------------------------------------------------------
        */

        $scoreStart = microtime(true);

        $rangeScores = $scoreCurrentPerformances->isNotEmpty()
            ? $scoreService->scoreRange(
                $scoreCurrentPerformances,
                $scoreComparisonPerformances
            )
            : collect();

        $rangeScore = $rangeScores->get($affiliate->id);

        Log::info('AFFILIATE DETAIL PERFORMANCE', [
            'step' => 'scoreRange',
            'seconds' => round(
                microtime(true) - $scoreStart,
                3
            ),
            'current_rows' => $scoreCurrentPerformances->count(),
            'comparison_rows' => $scoreComparisonPerformances->count(),
            'scores' => $rangeScores->count(),
            'target_found' => $rangeScore !== null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Aggregate Helper
        |--------------------------------------------------------------------------
        */

        $sum = function ($collection, string $field): float {
            return (float) $collection->sum(
                fn ($item) => (float) ($item->{$field} ?? 0)
            );
        };

        /*
        |--------------------------------------------------------------------------
        | Current Range Aggregate
        |--------------------------------------------------------------------------
        */

        $currentGmv = $sum(
            $currentPerformances,
            'gmv'
        );

        $previousGmv = $sum(
            $previousPerformances,
            'gmv'
        );

        $currentOrders = $sum(
            $currentPerformances,
            'attributed_orders'
        );

        $currentProductsSold = $sum(
            $currentPerformances,
            'products_sold'
        );

        $currentBuyers = $sum(
            $currentPerformances,
            'buyers'
        );

        $currentCommission = $sum(
            $currentPerformances,
            'commission'
        );

        $currentImpressions = $sum(
            $currentPerformances,
            'impressions'
        );

        $currentVideoViews = $sum(
            $currentPerformances,
            'video_views'
        );

        /*
        |--------------------------------------------------------------------------
        | AOV
        |--------------------------------------------------------------------------
        */

        $currentAov = $currentOrders > 0
            ? $currentGmv / $currentOrders
            : 0;

        /*
        |--------------------------------------------------------------------------
        | CTR
        |--------------------------------------------------------------------------
        |
        | Weighted berdasarkan impressions.
        |
        */

        $currentCtr = 0;

        if ($currentImpressions > 0) {
            $weightedCtr = $currentPerformances->sum(
                function ($performance) {
                    return
                        (float) ($performance->ctr ?? 0)
                        *
                        (float) ($performance->impressions ?? 0);
                }
            );

            $currentCtr =
                $weightedCtr / $currentImpressions;
        }

        /*
        |--------------------------------------------------------------------------
        | CTOR
        |--------------------------------------------------------------------------
        |
        | Mempertahankan behavior controller lama:
        | average CTR/CTOR dari snapshot.
        |
        */

        $currentCtor = $currentPerformances->count() > 0
            ? $currentPerformances->avg(
                fn ($performance) =>
                    (float) ($performance->ctor ?? 0)
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Current Performance Breakdown
        |--------------------------------------------------------------------------
        */

        $currentPerformance = $currentPerformances->isNotEmpty()
            ? [
                'id' => null,

                'gmv' => $currentGmv,

                'gmv_live' => $sum(
                    $currentPerformances,
                    'gmv_live'
                ),

                'gmv_video' => $sum(
                    $currentPerformances,
                    'gmv_video'
                ),

                'gmv_product_card' => $sum(
                    $currentPerformances,
                    'gmv_product_card'
                ),

                'refund' => $sum(
                    $currentPerformances,
                    'refund'
                ),

                'attributed_orders' => $currentOrders,

                'products_sold' => $currentProductsSold,

                'aov' => $currentAov,

                'ctr' => $currentCtr,

                'ctor' => $currentCtor,

                'impressions' => $currentImpressions,

                'video_views' => $currentVideoViews,

                'buyers' => $currentBuyers,

                'commission' => $currentCommission,

                'live_count' => $sum(
                    $currentPerformances,
                    'live_count'
                ),

                'video_count' => $sum(
                    $currentPerformances,
                    'video_count'
                ),

                'showcase_products' => $sum(
                    $currentPerformances,
                    'showcase_products'
                ),

                'content_samples' => $sum(
                    $currentPerformances,
                    'content_samples'
                ),

                'samples_sent' => $sum(
                    $currentPerformances,
                    'samples_sent'
                ),

                'products_returned' => $sum(
                    $currentPerformances,
                    'products_returned'
                ),

                'period_start' =>
                    $startDate->format('Y-m-d'),

                'period_end' =>
                    $endDate->format('Y-m-d'),

                'movement' => [
                    'previous_gmv' =>
                        $previousGmv,

                    'gmv_change' =>
                        $currentGmv - $previousGmv,

                    'gmv_change_percent' =>
                        $previousGmv > 0
                            ? (
                                (
                                    $currentGmv
                                    - $previousGmv
                                )
                                / $previousGmv
                            ) * 100
                            : null,

                    'status' =>
                        $previousGmv > 0
                            ? (
                                $currentGmv > $previousGmv
                                    ? 'UP'
                                    : (
                                        $currentGmv < $previousGmv
                                            ? 'DOWN'
                                            : 'STABLE'
                                    )
                            )
                            : 'NO_BASELINE',
                ],
            ]
            : null;

        /*
        |--------------------------------------------------------------------------
        | Performance History
        |--------------------------------------------------------------------------
        |
        | Sudah sorted DESC dari query.
        |
        | Jadi previous performance cukup mengambil row berikutnya.
        | Tidak perlu filter + sort ulang untuk setiap row.
        |
        */

        $historyStart = microtime(true);

        $performanceHistory = $performances
            ->values()
            ->map(
                function ($performance, $index) use ($performances) {

                    $previousPerformance =
                        $performances->get($index + 1);

                    $previousGmv =
                        $previousPerformance?->gmv;

                    $change = $previousGmv !== null
                        ? (float) $performance->gmv
                            - (float) $previousGmv
                        : null;

                    $changePercent =
                        $previousGmv !== null
                        && (float) $previousGmv != 0
                            ? (
                                $change
                                / (float) $previousGmv
                            ) * 100
                            : null;

                    $movement = 'NO_BASELINE';

                    if ($changePercent !== null) {
                        if ($changePercent > 0) {
                            $movement = 'UP';
                        } elseif ($changePercent < 0) {
                            $movement = 'DOWN';
                        } else {
                            $movement = 'STABLE';
                        }
                    }

                    return [
                        'id' =>
                            $performance->id,

                        'gmv' =>
                            $performance->gmv,

                        'gmv_live' =>
                            $performance->gmv_live,

                        'gmv_video' =>
                            $performance->gmv_video,

                        'gmv_product_card' =>
                            $performance->gmv_product_card,

                        'refund' =>
                            $performance->refund,

                        'attributed_orders' =>
                            $performance->attributed_orders,

                        'products_sold' =>
                            $performance->products_sold,

                        'aov' =>
                            $performance->aov,

                        'ctr' =>
                            $performance->ctr,

                        'ctor' =>
                            $performance->ctor,

                        'impressions' =>
                            $performance->impressions,

                        'video_views' =>
                            $performance->video_views,

                        'buyers' =>
                            $performance->buyers,

                        'commission' =>
                            $performance->commission,

                        'live_count' =>
                            $performance->live_count,

                        'video_count' =>
                            $performance->video_count,

                        'showcase_products' =>
                            $performance->showcase_products,

                        'content_samples' =>
                            $performance->content_samples,

                        'samples_sent' =>
                            $performance->samples_sent,

                        'products_returned' =>
                            $performance->products_returned,

                        'period_start' =>
                            $performance->period_start
                                ? Carbon::parse(
                                    $performance->period_start
                                )->format('Y-m-d')
                                : null,

                        'period_end' =>
                            $performance->period_end
                                ? Carbon::parse(
                                    $performance->period_end
                                )->format('Y-m-d')
                                : null,

                        'movement' => [
                            'status' =>
                                $movement,

                            'previous_gmv' =>
                                $previousGmv !== null
                                    ? (float) $previousGmv
                                    : null,

                            'gmv_change' =>
                                $change,

                            'gmv_change_percent' =>
                                $changePercent,
                        ],
                    ];
                }
            )
            ->values();

        Log::info('AFFILIATE DETAIL PERFORMANCE', [
            'step' => 'history mapping',
            'seconds' => round(
                microtime(true) - $historyStart,
                3
            ),
            'rows' => $performanceHistory->count(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Response Score
        |--------------------------------------------------------------------------
        */

        $score = $rangeScore
            ? [
                'performance_score' =>
                    $rangeScore['performance_score'],

                'growth_score' =>
                    $rangeScore['growth_score'],

                'consistency_score' =>
                    $rangeScore['consistency_score'],

                'opportunity_score' =>
                    $rangeScore['opportunity_score'],

                'action' =>
                    $rangeScore['action'],

                'growth_percent' =>
                    $rangeScore['growth_percent'],

                'period_count' =>
                    $rangeScore['period_count'],

                'generated_at' =>
                    now()->format('Y-m-d H:i'),

                'period_start' =>
                    $startDate->format('Y-m-d'),

                'period_end' =>
                    $endDate->format('Y-m-d'),

                'insights' =>
                    $rangeScore['insights'] ?? [],
            ]
            : null;

        /*
        |--------------------------------------------------------------------------
        | Total
        |--------------------------------------------------------------------------
        */

        Log::info('AFFILIATE DETAIL PERFORMANCE', [
            'step' => 'controller total',
            'seconds' => round(
                microtime(true) - $requestStart,
                3
            ),
            'affiliate_id' => $affiliate->id,
            'current_batches' => count($currentBatchIds),
            'comparison_batches' => count($comparisonBatchIds),
            'history_rows' => $performanceHistory->count(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render('affiliates/Show', [
            'affiliate' => [
                'id' =>
                    $affiliate->id,

                'name' =>
                    $affiliate->name,

                'username' =>
                    $affiliate->username,

                'platform' =>
                    $affiliate->platform,

                'status' =>
                    $affiliate->status,
            ],

            'latest_performance' =>
                $currentPerformance,

            'performance_history' =>
                $performanceHistory,

            'selected_period' => [
                'start' =>
                    $startDate->format('Y-m-d'),

                'end' =>
                    $endDate->format('Y-m-d'),
            ],

            'comparison_period' => [
                'start' =>
                    $previousStartDate->format('Y-m-d'),

                'end' =>
                    $previousEndDate->format('Y-m-d'),
            ],

            'score' =>
                $score,
            
            'back_url' =>
              $backUrl,
            
            'from' =>
                $from,
        ]);
    }
}