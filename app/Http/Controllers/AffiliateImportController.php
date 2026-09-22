<?php

namespace App\Http\Controllers;

use App\Imports\SellerCenterImport;
use App\Models\ImportBatch;
use App\Models\Affiliate;
use App\Services\AffiliateScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AffiliateImportController extends Controller
{
    public function store(
    Request $request,
    AffiliateScoreService $scoreService
    )
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

        /*
         * Buat batch import terlebih dahulu.
         */
        $batch = ImportBatch::create([
            'file_name' => $file->getClientOriginalName(),
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'uploaded_by' => Auth::id(),
            'status' => 'processing',
            'total_rows' => 0,
            'uploaded_at' => now(),
        ]);

        try {
            Excel::import(new SellerCenterImport($batch), $file);

            $totalRows = $batch->performances()->count();

            $batch->update([
                'status' => 'completed',
                'total_rows' => $totalRows,
            ]);

            $scoreService->scoreBatch($batch);

            return back()->with(
                'success',
                "Import berhasil. {$totalRows} data performance berhasil diproses."
            );
        } catch (\Throwable $e) {
            $batch->update([
                'status' => 'failed'
            ]);

            report($e);

            return back()->with(
                'error',
                'Import gagal. Silakan periksa format file dan data Seller Center.'
            );
        }
    }
}