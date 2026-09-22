<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HistoryController extends Controller
{
    public function index(Request $request): Response
    {
        $batches = ImportBatch::query()
            ->with('uploader:id,name')
            ->withCount('performances')
            ->latest('id')
            ->paginate(20);

        $batches->through(function ($batch) {
            return [
                'id' => $batch->id,
                'file_name' => $batch->file_name,
                'period_start' => $batch->period_start?->format('d M Y'),
                'period_end' => $batch->period_end?->format('d M Y'),
                'status' => $batch->status,
                'total_rows' => $batch->total_rows,
                'performance_count' => $batch->performances_count,
                'uploaded_by' => $batch->uploader?->name ?? '-',
                'uploaded_at' => $batch->uploaded_at?->format('d M Y H:i'),
            ];
        });

        return Inertia::render('history/Index', [
            'batches' => $batches,
        ]);
    }
        public function show(int $batch): Response
    {
        $batch = ImportBatch::findOrFail($batch);

        $summary = [
            'total_affiliates' => $batch->performances()
                ->distinct('affiliate_id')
                ->count('affiliate_id'),

            'total_gmv' => $batch->performances()
                ->sum('gmv'),

            'total_orders' => $batch->performances()
                ->sum('attributed_orders'),

            'total_buyers' => $batch->performances()
                ->sum('buyers'),

            'total_commission' => $batch->performances()
                ->sum('commission'),
        ];

        $performances = $batch->performances()
            ->with('affiliate:id,name,username,platform')
            ->orderByDesc('gmv')
            ->paginate(20)
            ->through(function ($performance) {
                return [
                    'id' => $performance->id,

                    'affiliate' => $performance->affiliate ? [
                        'id' => $performance->affiliate->id,
                        'name' => $performance->affiliate->name,
                        'username' => $performance->affiliate->username,
                        'platform' => $performance->affiliate->platform,
                    ] : null,

                    'gmv' => $performance->gmv,
                    'attributed_orders' => $performance->attributed_orders,
                    'buyers' => $performance->buyers,
                    'aov' => $performance->aov,
                    'ctr' => $performance->ctr,
                    'ctor' => $performance->ctor,
                    'commission' => $performance->commission,
                ];
            });

        return Inertia::render('history/Show', [
            'batch' => [
                'id' => $batch->id,
                'file_name' => $batch->file_name,
                'period_start' => $batch->period_start?->format('d M Y'),
                'period_end' => $batch->period_end?->format('d M Y'),
                'status' => $batch->status,
                'total_rows' => $batch->total_rows,
                'uploaded_at' => $batch->uploaded_at?->format('d M Y H:i'),
                'uploaded_by' => $batch->uploader?->name ?? '-',
            ],

            'summary' => $summary,

            'performances' => $performances,
        ]);
    }
    public function destroy(int $batch)
    {
        $batch = ImportBatch::findOrFail($batch);

        $batch->performances()->delete();

        $batch->scores()->delete();

        $batch->delete();

        return redirect()
            ->route('history.index')
            ->with('success', 'History import berhasil dihapus.');
    }
}