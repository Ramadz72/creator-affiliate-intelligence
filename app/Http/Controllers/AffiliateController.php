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
        /*
        |--------------------------------------------------------------------------
        | Import Batch
        |--------------------------------------------------------------------------
        */

        $latestBatch = ImportBatch::query()
            ->where('status', 'completed')
            ->where('uploaded_by', Auth::id())
            ->latest('id')
            ->first();

        $batchId = $request->input('batch_id');

        $selectedBatch = $batchId
            ? ImportBatch::query()
                ->where('status', 'completed')
                ->where('uploaded_by', Auth::id())
                ->where('id', $batchId)
                ->first()
            : $latestBatch;

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
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'opportunity' => 'affiliate_scores.opportunity_score',
            'performance' => 'affiliate_scores.performance_score',
            'gmv' => 'affiliate_performances.gmv',
            'orders' => 'affiliate_performances.attributed_orders',
            'aov' => 'affiliate_performances.aov',
            'ctr' => 'affiliate_performances.ctr',
            'ctor' => 'affiliate_performances.ctor',
            'name' => 'affiliates.name',
        ];

        if (!array_key_exists($sort, $allowedSorts)) {
            $sort = 'opportunity';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        /*
        |--------------------------------------------------------------------------
        | Action Filter
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
        | No Data
        |--------------------------------------------------------------------------
        */

        if (!$selectedBatch) {
            return Inertia::render('affiliates/Index', [
                'affiliates' => [
                    'data' => [],
                    'total' => 0,
                    'per_page' => 20,
                    'current_page' => 1,
                    'last_page' => 1,
                ],

                'latest_period' => null,

                'selected_period' => null,

                'latest_batch_id' => null,

                'selected_batch_id' => null,

                'import_batches' => $importBatches,

                'search' => $search,

                'sort' => $sort,

                'direction' => $direction,

                'action' => $action,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Affiliate Performance
        |--------------------------------------------------------------------------
        */

        $performances = AffiliatePerformance::query()
            ->with([
                'affiliate:id,name,username,platform,status',
            ])
            ->where(
                'affiliate_performances.import_batch_id',
                $selectedBatch->id
            )
            ->whereHas('affiliate', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('affiliate', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->leftJoin('affiliate_scores', function ($join) use ($selectedBatch) {
                $join->on(
                    'affiliate_performances.affiliate_id',
                    '=',
                    'affiliate_scores.affiliate_id'
                )
                ->where(
                    'affiliate_scores.import_batch_id',
                    '=',
                    $selectedBatch->id
                );
            })
            ->when($action !== '', function ($query) use ($action) {
                $query->where('affiliate_scores.action', $action);
            })
            ->leftJoin(
                'affiliates',
                'affiliate_performances.affiliate_id',
                '=',
                'affiliates.id'
            )
            ->select([
                'affiliate_performances.*',
                'affiliate_scores.performance_score',
                'affiliate_scores.opportunity_score',
                'affiliate_scores.action',
            ])
            ->orderBy($allowedSorts[$sort], $direction)
            ->paginate(20)
            ->withQueryString()
            ->through(function ($performance) use ($selectedBatch) {
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

                        'period_start' => $selectedBatch->period_start?->format('Y-m-d'),

                        'period_end' => $selectedBatch->period_end?->format('Y-m-d'),
                    ],
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render('affiliates/Index', [
            'affiliates' => $performances,

            // Tetap dipertahankan untuk kompatibilitas dengan UI lama
            'latest_period' => $latestBatch
                ? [
                    'start' => $latestBatch->period_start?->format('Y-m-d'),
                    'end' => $latestBatch->period_end?->format('Y-m-d'),
                ]
                : null,

            // Periode yang sedang aktif ditampilkan
            'selected_period' => [
                'start' => $selectedBatch->period_start?->format('Y-m-d'),
                'end' => $selectedBatch->period_end?->format('Y-m-d'),
            ],

            // Batch terbaru tetap disimpan
            'latest_batch_id' => $latestBatch?->id,

            // Batch yang sedang dipilih
            'selected_batch_id' => $selectedBatch->id,

            // Semua periode yang bisa dipilih
            'import_batches' => $importBatches,

            'search' => $search,

            'sort' => $sort,

            'direction' => $direction,

            'action' => $action,
        ]);
    }
}