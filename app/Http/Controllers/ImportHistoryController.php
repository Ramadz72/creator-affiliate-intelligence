<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use Inertia\Inertia;
use Inertia\Response;

class ImportHistoryController extends Controller
{
    public function index(): Response
    {
        $batches = ImportBatch::query()
            ->with('uploader:id,name')
            ->withCount('performances')
            ->latest('created_at')
            ->paginate(15)
            ->through(function ($batch) {
                return [
                    'id' => $batch->id,
                    'file_name' => $batch->file_name,
                    'period_start' => $batch->period_start?->format('Y-m-d'),
                    'period_end' => $batch->period_end?->format('Y-m-d'),
                    'status' => $batch->status,
                    'total_rows' => $batch->total_rows,
                    'performance_count' => $batch->performances_count,
                    'uploaded_by' => $batch->uploader?->name ?? 'Unknown',
                    'uploaded_at' => $batch->uploaded_at?->format('Y-m-d H:i'),
                ];
            });

        return Inertia::render('history/Index', [
            'batches' => $batches,
        ]);
    }
}