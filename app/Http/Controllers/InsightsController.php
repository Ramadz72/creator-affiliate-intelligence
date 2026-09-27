<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use App\Services\InsightEngine;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InsightsController extends Controller
{
    public function index(Request $request, InsightEngine $insightEngine)
    {
        $user = $request->user();

        $batch = ImportBatch::query()
            ->where('uploaded_by', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('period_start')
            ->whereNotNull('period_end')
            ->orderByDesc('period_end')
            ->first();

        $insight = $batch
            ? $insightEngine->generateForBatch($batch)
            : null;

        return Inertia::render('Insights/Index', [
            'insight' => $insight,
            'batch' => $batch ? [
                'id' => $batch->id,
                'file_name' => $batch->file_name,
                'period_start' => $batch->period_start?->format('Y-m-d'),
                'period_end' => $batch->period_end?->format('Y-m-d'),
            ] : null,
        ]);
    }
}