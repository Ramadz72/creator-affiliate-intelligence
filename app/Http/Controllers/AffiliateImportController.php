<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAffiliateImportJob;
use App\Models\ImportBatch;
use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AffiliateImportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:20480',
            ],

            'period_start' => [
                'required',
                'date',
            ],

            'period_end' => [
                'required',
                'date',
                'after_or_equal:period_start',
            ],
        ]);

        $file = $validated['file'];

        $batch = ImportBatch::create([
            'file_name' => $file->getClientOriginalName(),
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'uploaded_by' => Auth::id(),
            'status' => 'queued',
            'total_rows' => 0,
            'processed_rows' => 0,
            'successful_rows' => 0,
            'skipped_rows' => 0,
            'error_rows' => 0,
            'progress' => 0,
            'uploaded_at' => now(),
        ]);

        try {
            $filePath = $file->store(
                'imports',
                'local'
            );

            ProcessAffiliateImportJob::dispatch(
                $batch->id,
                $filePath
            );

            return back()->with([
                'success' => 'File berhasil diunggah. Proses import sedang berjalan.',
                'import_batch_id' => $batch->id,
            ]);
        } catch (\Throwable $e) {
            $batch->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            report($e);

            return back()->with(
                'error',
                'File gagal diproses. Silakan coba lagi.'
            );
        }
    }

    public function progress(ImportBatch $batch)
    {
        abort_unless(
            $batch->uploaded_by === Auth::id(),
            403
        );

        return response()->json([
            'id' => $batch->id,
            'status' => $batch->status,
            'progress' => (int) $batch->progress,

            'total_rows' => (int) $batch->total_rows,
            'processed_rows' => (int) $batch->processed_rows,
            'successful_rows' => (int) $batch->successful_rows,
            'skipped_rows' => (int) $batch->skipped_rows,
            'error_rows' => (int) $batch->error_rows,

            'started_at' => $batch->started_at?->toISOString(),
            'completed_at' => $batch->completed_at?->toISOString(),
            'error_message' => $batch->error_message,
        ]);
    }
}