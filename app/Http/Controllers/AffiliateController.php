<?php

namespace App\Http\Controllers;

use App\Models\AffiliatePerformance;
use App\Models\ImportBatch;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AffiliateController extends Controller
{
    public function index(Request $request): Response
    {
        $latestBatch = ImportBatch::query()
            ->where('status', 'completed')
            ->where('uploaded_by', Auth::id())
            ->latest('id')
            ->first();
        
            $search = trim((string) $request->input('search', ''));

        if (!$latestBatch) {
            return Inertia::render('affiliates/Index', [
                'affiliates' => [
                    'data' => [],
                    'total' => 0,
                    'per_page' => 20,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
                'latest_period' => null,
                'latest_batch_id' => null,
            ]);
        }

        $performances = AffiliatePerformance::query()
            ->with([
                'affiliate:id,name,username,platform,status',
            ])
            ->where('affiliate_performances.import_batch_id', $latestBatch->id)
            ->whereHas('affiliate', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('affiliate', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->leftJoin('affiliate_scores', function ($join) use ($latestBatch) {
                $join->on(
                    'affiliate_performances.affiliate_id',
                    '=',
                    'affiliate_scores.affiliate_id'
                )
                ->where(
                    'affiliate_scores.import_batch_id',
                    '=',
                    $latestBatch->id
                );
            })
            ->select([
                'affiliate_performances.*',
                'affiliate_scores.performance_score',
                'affiliate_scores.opportunity_score',
                'affiliate_scores.action',
            ])
            ->orderByDesc('affiliate_scores.opportunity_score')
            ->paginate(20)
            ->through(function ($performance) use ($latestBatch) {
                $affiliate = $performance->affiliate;

                return [
                    'id' => $affiliate?->id,
                    'name' => $affiliate?->name ?? '-',
                    'username' => $affiliate?->username ?? '-',
                    'platform' => $affiliate?->platform ?? 'TikTok',
                    'status' => $affiliate?->status ?? 'active',

                    'score' => [
                        'performance' => $performance->performance_score !== null
                            ? (float) $performance->performance_score
                            : null,

                        'opportunity' => $performance->opportunity_score !== null
                            ? (float) $performance->opportunity_score
                            : null,

                        'action' => $performance->action ?? 'MONITOR',
                    ],

                    'latest_performance' => [
                        'gmv' => $performance->gmv,
                        'attributed_orders' => $performance->attributed_orders,
                        'products_sold' => $performance->products_sold,
                        'aov' => $performance->aov,
                        'ctr' => $performance->ctr,
                        'ctor' => $performance->ctor,
                        'period_start' => $latestBatch->period_start?->format('Y-m-d'),
                        'period_end' => $latestBatch->period_end?->format('Y-m-d'),
                    ],
                ];
            });

        return Inertia::render('affiliates/Index', [
            'affiliates' => $performances,
            'latest_period' => [
                'start' => $latestBatch->period_start?->format('Y-m-d'),
                'end' => $latestBatch->period_end?->format('Y-m-d'),
            ],
            'latest_batch_id' => $latestBatch->id,
            'search' => $search,
        ]);
    }
}