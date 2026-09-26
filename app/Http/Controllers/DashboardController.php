<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Campaign;
use App\Models\Creator;
use App\Models\AffiliatePerformance;
use App\Models\AffiliateScore;
use App\Models\CreatorScore;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $period = $request->string('period')->toString();

        if ($period === '') {
            $period = session('dashboard_period', 'this_week');
        }

        if (! in_array($period, [
            'this_week',
            'last_week',
            'this_month',
            'last_month',
            'last_year',
            'custom',
        ], true)) {
            $period = 'this_week';
        }

        session()->put('dashboard_period', $period);

        $startDate = null;
        $endDate = null;

        switch ($period) {
            case 'this_week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;

            case 'last_week':
                $startDate = Carbon::now()->subWeek()->startOfWeek();
                $endDate = Carbon::now()->subWeek()->endOfWeek();
                break;

            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;

            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;

            case 'last_year':
                $startDate = Carbon::now()->subYear()->startOfYear();
                $endDate = Carbon::now()->subYear()->endOfYear();
                break;

            case 'custom':
                $customStartDate = $request->filled('start_date')
                    ? Carbon::parse($request->input('start_date'))
                    : (session('dashboard_custom_start_date')
                        ? Carbon::parse(session('dashboard_custom_start_date'))
                        : null);

                $customEndDate = $request->filled('end_date')
                    ? Carbon::parse($request->input('end_date'))
                    : (session('dashboard_custom_end_date')
                        ? Carbon::parse(session('dashboard_custom_end_date'))
                        : null);

                if ($customStartDate && $customEndDate && $customStartDate <= $customEndDate) {

                    session()->put('dashboard_custom_start_date', $customStartDate->format('Y-m-d'));
                    session()->put('dashboard_custom_end_date', $customEndDate->format('Y-m-d'));
                } else {
                    $period = 'this_week';

                    session()->put('dashboard_period', $period);

                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                }

                break;
        }

        $totalCreators = Creator::where('user_id', $user->id)->count();

        $totalAffiliates = Affiliate::where('user_id', $user->id)->count();

        $activeCampaigns = Campaign::query()
        ->whereHas('creator', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('status', 'Running')
        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            $query
                ->whereDate('start_date', '<=', $endDate)
                ->whereDate('end_date', '>=', $startDate);
        })
        ->count();

        /*
        |--------------------------------------------------------------------------
        | Advanced Dashboard Metrics
        |--------------------------------------------------------------------------
        */

        $totalGmv = AffiliatePerformance::query()
            ->whereHas('affiliate', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereHas('importBatch', function ($batchQuery) use ($startDate, $endDate) {
                    $batchQuery
                        ->whereDate('period_start', '>=', $startDate)
                        ->whereDate('period_end', '<=', $endDate);
                });
            })
            ->sum('gmv');

        $totalOrders = AffiliatePerformance::query()
            ->whereHas('affiliate', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereHas('importBatch', function ($batchQuery) use ($startDate, $endDate) {
                    $batchQuery
                        ->whereDate('period_start', '>=', $startDate)
                        ->whereDate('period_end', '<=', $endDate);
                });
            })
            ->sum('attributed_orders');

        $avgCreatorScore = CreatorScore::query()
            ->whereHas('creator', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query
                    ->whereDate('period_start', '>=', $startDate)
                    ->whereDate('period_end', '<=', $endDate);
            })
            ->whereIn('id', function ($query) use ($startDate, $endDate) {
                $query
                    ->selectRaw('MAX(id)')
                    ->from('creator_scores')
                    ->when($startDate && $endDate, function ($subQuery) use ($startDate, $endDate) {
                        $subQuery
                            ->whereDate('period_start', '>=', $startDate)
                            ->whereDate('period_end', '<=', $endDate);
                    })
                    ->groupBy('creator_id');
            })
            ->avg('overall_score');

        $avgAffiliateOpportunity = AffiliateScore::query()
            ->whereHas('affiliate', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereHas('importBatch', function ($batchQuery) use ($startDate, $endDate) {
                    $batchQuery
                        ->whereDate('period_start', '>=', $startDate)
                        ->whereDate('period_end', '<=', $endDate);
                });
            })
            ->whereIn('id', function ($query) use ($startDate, $endDate) {
                $query
                    ->selectRaw('MAX(id)')
                    ->from('affiliate_scores')
                    ->when($startDate && $endDate, function ($subQuery) use ($startDate, $endDate) {
                        $subQuery->whereIn('import_batch_id', function ($batchQuery) use ($startDate, $endDate) {
                            $batchQuery
                                ->select('id')
                                ->from('import_batches')
                                ->whereDate('period_start', '>=', $startDate)
                                ->whereDate('period_end', '<=', $endDate);
                        });
                    })
                    ->groupBy('affiliate_id');
            })
            ->avg('opportunity_score');
        
        /*
        |--------------------------------------------------------------------------
        | Previous Period Comparison
        |--------------------------------------------------------------------------
        */

        $previousStartDate = null;
        $previousEndDate = null;

        if ($startDate && $endDate) {
            $periodDays = $startDate->diffInDays($endDate) + 1;

            $previousEndDate = $startDate->copy()->subDay();
            $previousStartDate = $previousEndDate->copy()->subDays($periodDays - 1);
        }

        $previousGmv = AffiliatePerformance::query()
            ->whereHas('affiliate', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($previousStartDate && $previousEndDate, function ($query) use ($previousStartDate, $previousEndDate) {
                $query->whereHas('importBatch', function ($batchQuery) use ($previousStartDate, $previousEndDate) {
                    $batchQuery
                        ->whereDate('period_start', '>=', $previousStartDate)
                        ->whereDate('period_end', '<=', $previousEndDate);
                });
            })
            ->sum('gmv');

        $previousOrders = AffiliatePerformance::query()
            ->whereHas('affiliate', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($previousStartDate && $previousEndDate, function ($query) use ($previousStartDate, $previousEndDate) {
                $query->whereHas('importBatch', function ($batchQuery) use ($previousStartDate, $previousEndDate) {
                    $batchQuery
                        ->whereDate('period_start', '>=', $previousStartDate)
                        ->whereDate('period_end', '<=', $previousEndDate);
                });
            })
            ->sum('attributed_orders');

        $gmvGrowth = $previousGmv > 0
            ? (($totalGmv - $previousGmv) / $previousGmv) * 100
            : null;

        $ordersGrowth = $previousOrders > 0
            ? (($totalOrders - $previousOrders) / $previousOrders) * 100
            : null;

       $affiliatePerformance = AffiliatePerformance::query()
        ->whereHas('affiliate', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            $query->whereHas('importBatch', function ($batchQuery) use ($startDate, $endDate) {
                $batchQuery
                    ->whereDate('period_start', '>=', $startDate)
                    ->whereDate('period_end', '<=', $endDate);
            });
        })
        ->selectRaw('import_batch_id, SUM(gmv) as total_gmv')
        ->groupBy('import_batch_id')
        ->with('importBatch:id,period_start,period_end')
        ->orderByDesc('import_batch_id')
        ->limit(7)
        ->get()
        ->sortBy('import_batch_id')
        ->values()
        ->map(function ($performance) {
            return [
                'batch_id' => $performance->import_batch_id,
                'period_start' => $performance->importBatch?->period_start?->format('Y-m-d'),
                'period_end' => $performance->importBatch?->period_end?->format('Y-m-d'),
                'gmv' => (float) $performance->total_gmv,
            ];
        });

        $creatorReviewCount = CreatorScore::query()
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query
                    ->whereDate('period_start', '>=', $startDate)
                    ->whereDate('period_end', '<=', $endDate);
            })
            ->whereIn('id', function ($query) use ($startDate, $endDate) {
                $query
                    ->selectRaw('MAX(id)')
                    ->from('creator_scores')
                    ->when($startDate && $endDate, function ($subQuery) use ($startDate, $endDate) {
                        $subQuery
                            ->whereDate('period_start', '>=', $startDate)
                            ->whereDate('period_end', '<=', $endDate);
                    })
                    ->groupBy('creator_id');
            })
            ->whereHas('creator', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereIn('recommendation', [
                'negotiate',
                'not_recommended',
            ])
            ->count();

        $affiliateActionCounts = AffiliateScore::query()
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereHas('importBatch', function ($batchQuery) use ($startDate, $endDate) {
                    $batchQuery
                        ->whereDate('period_start', '>=', $startDate)
                        ->whereDate('period_end', '<=', $endDate);
                });
            })
            ->whereIn('id', function ($query) use ($startDate, $endDate) {
                $query
                    ->selectRaw('MAX(id)')
                    ->from('affiliate_scores')
                    ->when($startDate && $endDate, function ($subQuery) use ($startDate, $endDate) {
                        $subQuery->whereIn('import_batch_id', function ($batchQuery) use ($startDate, $endDate) {
                            $batchQuery
                                ->select('id')
                                ->from('import_batches')
                                ->whereDate('period_start', '>=', $startDate)
                                ->whereDate('period_end', '<=', $endDate);
                        });
                    })
                    ->groupBy('affiliate_id');
            })
            ->whereHas('affiliate', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->selectRaw('action, COUNT(*) as total')
            ->groupBy('action')
            ->pluck('total', 'action');

        $actionRequired = [
            'creators_to_review' => $creatorReviewCount,
            'affiliates_to_support' => (int) ($affiliateActionCounts['SUPPORT'] ?? 0),
            'need_monitoring' => (int) ($affiliateActionCounts['MONITOR'] ?? 0),
            'deprioritize' => (int) ($affiliateActionCounts['DEPRIORITIZE'] ?? 0),
        ];

        $creatorOverview = CreatorScore::query()
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query
                    ->whereDate('period_start', '>=', $startDate)
                    ->whereDate('period_end', '<=', $endDate);
            })
            ->whereIn('id', function ($query) use ($startDate, $endDate) {
                $query
                    ->selectRaw('MAX(id)')
                    ->from('creator_scores')
                    ->when($startDate && $endDate, function ($subQuery) use ($startDate, $endDate) {
                        $subQuery
                            ->whereDate('period_start', '>=', $startDate)
                            ->whereDate('period_end', '<=', $endDate);
                    })
                    ->groupBy('creator_id');
            })
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
        
        $affiliateOverview = AffiliateScore::query()
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereHas('importBatch', function ($batchQuery) use ($startDate, $endDate) {
                    $batchQuery
                        ->whereDate('period_start', '>=', $startDate)
                        ->whereDate('period_end', '<=', $endDate);
                });
            })
            ->whereIn('id', function ($query) use ($startDate, $endDate) {
                $query
                    ->selectRaw('MAX(id)')
                    ->from('affiliate_scores')
                    ->when($startDate && $endDate, function ($subQuery) use ($startDate, $endDate) {
                        $subQuery->whereIn('import_batch_id', function ($batchQuery) use ($startDate, $endDate) {
                            $batchQuery
                                ->select('id')
                                ->from('import_batches')
                                ->whereDate('period_start', '>=', $startDate)
                                ->whereDate('period_end', '<=', $endDate);
                        });
                    })
                    ->groupBy('affiliate_id');
            })
            ->whereHas('affiliate', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with([
                'affiliate:id,name,username,platform',
                'importBatch:id,period_start,period_end',
            ])
            ->orderByDesc('overall_score')
            ->limit(2)
            ->get()
            ->map(function ($score) {
                $performance = AffiliatePerformance::query()
                    ->where('affiliate_id', $score->affiliate_id)
                    ->where('import_batch_id', $score->import_batch_id)
                    ->first();

                return [
                    'id' => $score->affiliate_id,
                    'name' => $score->affiliate?->name,
                    'username' => $score->affiliate?->username,
                    'platform' => $score->affiliate?->platform,
                    'score' => (float) $score->overall_score,
                    'action' => $score->action,
                    'gmv' => $performance ? (float) $performance->gmv : 0,
                ];
            })
            ->values();

        $customStartDate = $startDate?->format('Y-m-d');
        $customEndDate = $endDate?->format('Y-m-d');
                
        return Inertia::render('Dashboard', [
            'stats' => [
                'creators' => $totalCreators,
                'affiliates' => $totalAffiliates,
                'campaigns' => $activeCampaigns,
                'gmv' => (float) $totalGmv,
                'orders' => (int) $totalOrders,
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
            
            'selected_period' => $period,
            'custom_start_date' => $period === 'custom' ? $customStartDate : null,
            'custom_end_date' => $period === 'custom' ? $customEndDate : null,
            'affiliate_performance' => $affiliatePerformance,
            'action_required' => $actionRequired,
            'creator_overview' => $creatorOverview,
            'affiliate_overview' => $affiliateOverview,
        ]);
    }
}