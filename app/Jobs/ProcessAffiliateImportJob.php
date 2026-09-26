<?php

namespace App\Jobs;

use App\Imports\SellerCenterImport;
use App\Models\ImportBatch;
use App\Services\AffiliateScoreService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ProcessAffiliateImportJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(
        public int $batchId,
        public string $filePath,
    ) {
    }

    public function handle(AffiliateScoreService $scoreService): void
    {
        $batch = ImportBatch::findOrFail($this->batchId);

        try {
            $batch->update([
                'status' => 'processing',
                'started_at' => now(),
                'progress' => 0,
            ]);

            Excel::import(
                new SellerCenterImport($batch),
                $this->filePath,
                'local'
            );

            $batch->refresh();

            $totalRows = (int) $batch->total_rows;

            $batch->update([
                'status' => 'scoring',
            ]);

            $scoreService->scoreBatch($batch);

            $batch->update([
                'status' => 'completed',
                'progress' => 100,
                'completed_at' => now(),
                'total_rows' => $totalRows,
                'processed_rows' => max(
                    (int) $batch->processed_rows,
                    $totalRows
                ),
            ]);
        } catch (Throwable $e) {
            $batch->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            report($e);

            throw $e;
        } finally {
            if (Storage::disk('local')->exists($this->filePath)) {
                Storage::disk('local')->delete($this->filePath);
            }
        }
    }
}