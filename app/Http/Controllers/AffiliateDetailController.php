<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\ImportBatch;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AffiliateDetailController extends Controller
{
    public function show(Request $request, Affiliate $affiliate): Response
    {
        abort_unless($affiliate->user_id === Auth::id(), 404);

        /*
        |--------------------------------------------------------------------------
        | Date Range
        |--------------------------------------------------------------------------
        */

        $latestDate = ImportBatch::query()
            ->where('status', 'completed')
            ->where('uploaded_by', Auth::id())
            ->max('period_start');

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->startOfDay();
        } else {
            $startDate = $latestDate
                ? Carbon::parse($latestDate)->startOfDay()
                : now()->startOfDay();

            $endDate = $startDate->copy();
        }

        /*
        |--------------------------------------------------------------------------
        | Pastikan range valid
        |--------------------------------------------------------------------------
        */

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        /*
        |--------------------------------------------------------------------------
        | Previous Period
        |--------------------------------------------------------------------------
        |
        | Contoh:
        | 01 Sep - 10 Sep
        | sebelumnya:
        | 22 Agu - 31 Agu
        |
        */

        $days = $startDate->diffInDays($endDate) + 1;

        $previousEndDate = $startDate->copy()->subDay();

        $previousStartDate = $previousEndDate
            ->copy()
            ->subDays($days - 1);

        /*
        |--------------------------------------------------------------------------
        | Performance History
        |--------------------------------------------------------------------------
        */

        $performances = $affiliate->performances()
            ->with('importBatch:id,period_start,period_end,status')
            ->whereHas('importBatch', function ($query) {
                $query->where('status', 'completed');
            })
            ->get()
            ->sortByDesc(function ($performance) {
                return $performance->importBatch?->period_start;
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Current Range Performances
        |--------------------------------------------------------------------------
        */

        $currentPerformances = $performances->filter(function ($performance) use (
            $startDate,
            $endDate
        ) {
            $date = $performance->importBatch?->period_start;

            if (!$date) {
                return false;
            }

            return $date->between(
                $startDate,
                $endDate
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Previous Range Performances
        |--------------------------------------------------------------------------
        */

        $previousPerformances = $performances->filter(function ($performance) use (
            $previousStartDate,
            $previousEndDate
        ) {
            $date = $performance->importBatch?->period_start;

            if (!$date) {
                return false;
            }

            return $date->between(
                $previousStartDate,
                $previousEndDate
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Aggregate Current Range
        |--------------------------------------------------------------------------
        */

        $sum = function ($collection, $field) {
            return $collection->sum(function ($item) use ($field) {
                return (float) ($item->{$field} ?? 0);
            });
        };

        $currentGmv = $sum($currentPerformances, 'gmv');
        $previousGmv = $sum($previousPerformances, 'gmv');

        $currentOrders = $sum(
            $currentPerformances,
            'attributed_orders'
        );

        $previousOrders = $sum(
            $previousPerformances,
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

        /*
        |--------------------------------------------------------------------------
        | Helper Aggregate
        |--------------------------------------------------------------------------
        */

        $aggregate = function ($field) use ($currentPerformances) {
            return $currentPerformances->sum(function ($performance) use ($field) {
                return (float) ($performance->{$field} ?? 0);
            });
        };

        /*
        |--------------------------------------------------------------------------
        | Average Metrics
        |--------------------------------------------------------------------------
        */

        $currentImpressions = $aggregate('impressions');
        $currentVideoViews = $aggregate('video_views');

        /*
        | AOV = total GMV / total orders
        */

        $currentAov = $currentOrders > 0
            ? $currentGmv / $currentOrders
            : 0;

        /*
        | CTR = total clicks / total impressions
        |
        | Karena data existing hanya menyimpan CTR,
        | gunakan weighted average berdasarkan impressions.
        */

        $currentCtr = 0;

        if ($currentImpressions > 0) {
            $weightedCtr = $currentPerformances->sum(function ($performance) {
                return (
                    (float) ($performance->ctr ?? 0)
                ) * (
                    (float) ($performance->impressions ?? 0)
                );
            });

            $currentCtr = $weightedCtr / $currentImpressions;
        }

        /*
        | CTOR = weighted average berdasarkan video views/impressions
        */

        $currentCtor = $currentPerformances->count() > 0
            ? $currentPerformances->avg(function ($performance) {
                return (float) ($performance->ctor ?? 0);
            })
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Current Range Breakdown
        |--------------------------------------------------------------------------
        */

        $currentPerformance = $currentPerformances->isNotEmpty()
            ? [
                'id' => null,

                'gmv' => $currentGmv,

                'gmv_live' => $aggregate('gmv_live'),
                'gmv_video' => $aggregate('gmv_video'),
                'gmv_product_card' => $aggregate('gmv_product_card'),

                'refund' => $aggregate('refund'),

                'attributed_orders' => $currentOrders,
                'products_sold' => $currentProductsSold,

                'aov' => $currentAov,

                'ctr' => $currentCtr,
                'ctor' => $currentCtor,

                'impressions' => $currentImpressions,
                'video_views' => $currentVideoViews,

                'buyers' => $currentBuyers,

                'commission' => $currentCommission,

                'live_count' => $aggregate('live_count'),
                'video_count' => $aggregate('video_count'),
                'showcase_products' => $aggregate('showcase_products'),

                'content_samples' => $aggregate('content_samples'),
                'samples_sent' => $aggregate('samples_sent'),
                'products_returned' => $aggregate('products_returned'),

                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),

                'movement' => [
                    'previous_gmv' => $previousGmv,
                    'gmv_change' => $currentGmv - $previousGmv,
                    'gmv_change_percent' => $previousGmv > 0
                        ? (($currentGmv - $previousGmv) / $previousGmv) * 100
                        : null,

                    'status' => $previousGmv > 0
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
        | Latest Batch Inside Selected Range
        |--------------------------------------------------------------------------
        |
        | Score tetap mengambil score dari snapshot terakhir
        | yang tersedia dalam range tersebut.
        |
        */

        $selectedBatch = ImportBatch::query()
            ->where('status', 'completed')
            ->where('uploaded_by', Auth::id())
            ->whereDate('period_start', '>=', $startDate)
            ->whereDate('period_start', '<=', $endDate)
            ->orderByDesc('period_start')
            ->orderByDesc('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Score
        |--------------------------------------------------------------------------
        */

        $latestScore = $selectedBatch
            ? $affiliate->scores()
                ->with('importBatch:id,period_start,period_end')
                ->where(
                    'import_batch_id',
                    $selectedBatch->id
                )
                ->latest('id')
                ->first()
            : null;

        /*
        |--------------------------------------------------------------------------
        | Performance History
        |--------------------------------------------------------------------------
        */

        $performanceHistory = $performances
            ->map(function ($performance) use ($performances) {

                $currentDate = $performance
                    ->importBatch
                    ?->period_start
                    ?->format('Y-m-d');

                $previousPerformance = $performances
                    ->filter(function ($item) use ($currentDate) {
                        $date = $item
                            ->importBatch
                            ?->period_start
                            ?->format('Y-m-d');

                        return $date && $date < $currentDate;
                    })
                    ->sortByDesc(function ($item) {
                        return $item
                            ->importBatch
                            ?->period_start;
                    })
                    ->first();

                $previousGmv = $previousPerformance?->gmv;

                $change = $previousGmv !== null
                    ? (float) $performance->gmv - (float) $previousGmv
                    : null;

                $changePercent = (
                    $previousGmv !== null &&
                    (float) $previousGmv != 0
                )
                    ? (
                        $change / (float) $previousGmv
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
                    'id' => $performance->id,

                    'gmv' => $performance->gmv,
                    'gmv_live' => $performance->gmv_live,
                    'gmv_video' => $performance->gmv_video,
                    'gmv_product_card' => $performance->gmv_product_card,

                    'refund' => $performance->refund,

                    'attributed_orders' => $performance->attributed_orders,
                    'products_sold' => $performance->products_sold,
                    'aov' => $performance->aov,

                    'ctr' => $performance->ctr,
                    'ctor' => $performance->ctor,

                    'impressions' => $performance->impressions,
                    'video_views' => $performance->video_views,
                    'buyers' => $performance->buyers,

                    'commission' => $performance->commission,

                    'live_count' => $performance->live_count,
                    'video_count' => $performance->video_count,
                    'showcase_products' => $performance->showcase_products,

                    'content_samples' => $performance->content_samples,
                    'samples_sent' => $performance->samples_sent,
                    'products_returned' => $performance->products_returned,

                    'period_start' => $performance
                        ->importBatch
                        ?->period_start
                        ?->format('Y-m-d'),

                    'period_end' => $performance
                        ->importBatch
                        ?->period_end
                        ?->format('Y-m-d'),

                    'movement' => [
                        'status' => $movement,
                        'previous_gmv' => $previousGmv !== null
                            ? (float) $previousGmv
                            : null,

                        'gmv_change' => $change,

                        'gmv_change_percent' => $changePercent,
                    ],
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render('affiliates/Show', [
            'affiliate' => [
                'id' => $affiliate->id,
                'name' => $affiliate->name,
                'username' => $affiliate->username,
                'platform' => $affiliate->platform,
                'status' => $affiliate->status,
            ],

            'latest_performance' => $currentPerformance,

            'performance_history' => $performanceHistory,

            'selected_period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],

            'comparison_period' => [
                'start' => $previousStartDate->format('Y-m-d'),
                'end' => $previousEndDate->format('Y-m-d'),
            ],

            'score' => $latestScore ? [
                'performance_score' => $latestScore->performance_score,
                'growth_score' => $latestScore->growth_score,
                'consistency_score' => $latestScore->consistency_score,
                'opportunity_score' => $latestScore->opportunity_score,
                'action' => $latestScore->action,

                'generated_at' => $latestScore->generated_at
                    ?->format('Y-m-d H:i'),

                'period_start' => $latestScore->importBatch
                    ?->period_start
                    ?->format('Y-m-d'),

                'period_end' => $latestScore->importBatch
                    ?->period_end
                    ?->format('Y-m-d'),

                'insights' => [],

                'period_count' => $affiliate->performances()
                    ->whereHas('importBatch', function ($query) {
                        $query->where('status', 'completed');
                    })
                    ->distinct('import_batch_id')
                    ->count('import_batch_id'),
            ] : null,

            'selected_batch_id' => $selectedBatch?->id,
        ]);
    }
}