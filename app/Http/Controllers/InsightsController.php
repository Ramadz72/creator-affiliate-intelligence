<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use App\Services\InsightEngine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InsightsController extends Controller
{
    public function index(Request $request, InsightEngine $insightEngine)
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Latest Available Batch
        |--------------------------------------------------------------------------
        */

        $latestBatch = ImportBatch::query()
            ->where('uploaded_by', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('period_start')
            ->whereNotNull('period_end')
            ->orderByDesc('period_end')
            ->first();

        if (!$latestBatch) {
            return Inertia::render('Insights/Index', [
                'insight' => null,
                'batch' => null,
                'selected_period' => null,
                'comparison_period' => null,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Selected Period
        |--------------------------------------------------------------------------
        */

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))
            : $latestBatch->period_start->copy();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))
            : $latestBatch->period_end->copy();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [
                $endDate,
                $startDate,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Comparison Period
        |--------------------------------------------------------------------------
        */

        $days = $startDate->diffInDays($endDate) + 1;

        $previousEndDate = $startDate->copy()->subDay();

        $previousStartDate = $previousEndDate
            ->copy()
            ->subDays($days - 1);

        /*
        |--------------------------------------------------------------------------
        | Current Batches
        |--------------------------------------------------------------------------
        */

        $currentBatches = ImportBatch::query()
            ->where('uploaded_by', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('period_start')
            ->whereNotNull('period_end')
            ->whereDate('period_start', '>=', $startDate)
            ->whereDate('period_end', '<=', $endDate)
            ->orderBy('period_start')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Comparison Batches
        |--------------------------------------------------------------------------
        */

        $comparisonBatches = ImportBatch::query()
            ->where('uploaded_by', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('period_start')
            ->whereNotNull('period_end')
            ->whereDate('period_start', '>=', $previousStartDate)
            ->whereDate('period_end', '<=', $previousEndDate)
            ->orderBy('period_start')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Generate Insight
        |--------------------------------------------------------------------------
        */

        $insight = $currentBatches->isNotEmpty()
            ? $insightEngine->generateForPeriod(
                $currentBatches,
                $comparisonBatches
            )
            : null;

        return Inertia::render('Insights/Index', [
            'insight' => $insight,

            'batch' => $currentBatches->isNotEmpty()
                ? [
                    'id' => $currentBatches->first()->id,
                    'file_name' => $currentBatches->count() === 1
                        ? $currentBatches->first()->file_name
                        : $currentBatches->count() . ' daily snapshots',
                    'period_start' => $startDate->format('Y-m-d'),
                    'period_end' => $endDate->format('Y-m-d'),
                ]
                : null,

            'selected_period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],

            'comparison_period' => [
                'start' => $previousStartDate->format('Y-m-d'),
                'end' => $previousEndDate->format('Y-m-d'),
            ],
        ]);
    }
}