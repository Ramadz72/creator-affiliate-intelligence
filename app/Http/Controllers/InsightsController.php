<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use App\Services\InsightEngine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class InsightsController extends Controller
{
    public function index(Request $request, InsightEngine $insightEngine)
    {
        $requestStart = microtime(true);
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Latest Available Batch
        |--------------------------------------------------------------------------
        */

        $latestBatchStart = microtime(true);

        $latestBatch = ImportBatch::query()
            ->where('uploaded_by', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('period_start')
            ->whereNotNull('period_end')
            ->orderByDesc('period_end')
            ->first();

        Log::info('INSIGHTS PERFORMANCE', [
            'step' => 'latest batch query',
            'seconds' => round(microtime(true) - $latestBatchStart, 3),
        ]);

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

        $periodStart = microtime(true);

        $sessionKey = 'insights_period';

        $savedPeriod = $request->session()->get($sessionKey);

        $startInput = $request->input('start_date');
        $endInput = $request->input('end_date');

        if ($startInput && $endInput) {
            // Request dari date picker punya prioritas tertinggi
            $startDate = Carbon::parse($startInput)->startOfDay();
            $endDate = Carbon::parse($endInput)->startOfDay();

        } elseif (
            is_array($savedPeriod)
            && !empty($savedPeriod['start'])
            && !empty($savedPeriod['end'])
        ) {
            // Gunakan periode terakhir yang dipilih
            $startDate = Carbon::parse($savedPeriod['start'])->startOfDay();
            $endDate = Carbon::parse($savedPeriod['end'])->startOfDay();

        } else {
            // Pertama kali membuka Insights
            $startDate = $latestBatch->period_start->copy()->startOfDay();
            $endDate = $latestBatch->period_end->copy()->startOfDay();
        }

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [
                $endDate,
                $startDate,
            ];
        }

        // Simpan periode terakhir ke session
        $request->session()->put(
            $sessionKey,
            [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ]
        );

        Log::info('INSIGHTS PERFORMANCE', [
            'step' => 'period calculation',
            'seconds' => round(microtime(true) - $periodStart, 3),
        ]);

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

        $currentBatchStart = microtime(true);

        $currentBatches = ImportBatch::query()
            ->where('uploaded_by', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('period_start')
            ->whereNotNull('period_end')
            ->whereDate('period_start', '>=', $startDate)
            ->whereDate('period_end', '<=', $endDate)
            ->orderBy('period_start')
            ->get();

        Log::info('INSIGHTS PERFORMANCE', [
            'step' => 'current batches query',
            'seconds' => round(microtime(true) - $currentBatchStart, 3),
            'count' => $currentBatches->count(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Comparison Batches
        |--------------------------------------------------------------------------
        */

        $comparisonBatchStart = microtime(true);

        $comparisonBatches = ImportBatch::query()
            ->where('uploaded_by', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('period_start')
            ->whereNotNull('period_end')
            ->whereDate('period_start', '>=', $previousStartDate)
            ->whereDate('period_end', '<=', $previousEndDate)
            ->orderBy('period_start')
            ->get();

        Log::info('INSIGHTS PERFORMANCE', [
            'step' => 'comparison batches query',
            'seconds' => round(microtime(true) - $comparisonBatchStart, 3),
            'count' => $comparisonBatches->count(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Generate Insight
        |--------------------------------------------------------------------------
        */

        $engineStart = microtime(true);

        $insight = $currentBatches->isNotEmpty()
            ? $insightEngine->generateForPeriod(
                $currentBatches,
                $comparisonBatches
            )
            : null;

        Log::info('INSIGHTS PERFORMANCE', [
            'step' => 'generate insight',
            'seconds' => round(microtime(true) - $engineStart, 3),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Controller Total Before Inertia
        |--------------------------------------------------------------------------
        */

        Log::info('INSIGHTS PERFORMANCE', [
            'step' => 'controller before inertia',
            'seconds' => round(microtime(true) - $requestStart, 3),
        ]);

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