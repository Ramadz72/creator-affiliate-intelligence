<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\ImportBatch;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AffiliateDetailController extends Controller
{
        public function show(Request $request, Affiliate $affiliate): Response
    {   
        abort_unless($affiliate->user_id === Auth::id(), 404);
        
        $batchId = $request->input('batch_id');

        $selectedBatch = $batchId
            ? $affiliate->performances()
                ->with('importBatch:id,period_start,period_end,status')
                ->where('import_batch_id', $batchId)
                ->whereHas('importBatch', function ($query) {
                    $query->where('status', 'completed');
                })
                ->first()?->importBatch
            : null;

        $performances = $affiliate->performances()
            ->with('importBatch:id,period_start,period_end,status')
            ->whereHas('importBatch', function ($query) {
                $query->where('status', 'completed');
            })
            ->latest('import_batch_id')
            ->get()
            ->map(function ($performance) {
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
                    'period_start' => $performance->importBatch?->period_start?->format('Y-m-d'),
                    'period_end' => $performance->importBatch?->period_end?->format('Y-m-d'),
                ];
            });


        $latestPerformance = $selectedBatch
        ? $performances->first(function ($performance) use ($selectedBatch) {
            return $performance['period_start'] === $selectedBatch->period_start?->format('Y-m-d')
                && $performance['period_end'] === $selectedBatch->period_end?->format('Y-m-d');
        })
        : $performances->first();;

        $latestScore = $affiliate->scores()
            ->with('importBatch:id,period_start,period_end')
            ->when($selectedBatch, function ($query) use ($selectedBatch) {
                $query->where('import_batch_id', $selectedBatch->id);
            })
            ->latest('id')
            ->first();
            
        return Inertia::render('affiliates/Show', [
            'affiliate' => [
                'id' => $affiliate->id,
                'name' => $affiliate->name,
                'username' => $affiliate->username,
                'platform' => $affiliate->platform,
                'status' => $affiliate->status,
            ],
            
            'latest_performance' => $latestPerformance,

            'performance_history' => $performances->values(),

            'score' => $latestScore ? [
                'performance_score' => $latestScore->performance_score,
                'growth_score' => $latestScore->growth_score,
                'consistency_score' => $latestScore->consistency_score,
                'opportunity_score' => $latestScore->opportunity_score,
                'action' => $latestScore->action,
                'generated_at' => $latestScore->generated_at?->format('Y-m-d H:i'),
                'period_start' => $latestScore->importBatch?->period_start?->format('Y-m-d'),
                'period_end' => $latestScore->importBatch?->period_end?->format('Y-m-d'),
                'insights' => [],
                'period_count' => $affiliate->performances()
                    ->whereHas('importBatch', function ($query) {
                        $query->where('status', 'completed');
                    })
                    ->distinct('import_batch_id')
                    ->count('import_batch_id'),
            ] : null,
            'import_batches' => ImportBatch::query()
                ->where('status', 'completed')
                ->where('uploaded_by', Auth::id())
                ->orderByDesc('period_start')
                ->orderByDesc('id')
                ->get([
                    'id',
                    'file_name',
                    'period_start',
                    'period_end',
                ]),

            'selected_batch_id' => $selectedBatch?->id,
        ]);
    }
}