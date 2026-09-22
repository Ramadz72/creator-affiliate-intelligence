<?php

namespace App\Imports;

use App\Models\Affiliate;
use App\Models\AffiliatePerformance;
use App\Models\ImportBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SellerCenterImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function __construct(protected ImportBatch $batch)
    {
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $name = trim((string) ($row['creator_name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $affiliate = Affiliate::firstOrCreate(
                ['name' => $name],
                [
                    'username' => Str::slug($name),
                    'platform' => 'TikTok',
                    'status' => 'active',
                ]
            );

            AffiliatePerformance::create([
                'affiliate_id' => $affiliate->id,
                'import_batch_id' => $this->batch->id,

                'gmv' => $this->number($row['gmv_dari_kreator'] ?? 0),
                'gmv_live' => $this->number($row['gmv_dari_live_kreator'] ?? 0),
                'gmv_video' => $this->number($row['gmv_dari_video_afiliasi'] ?? 0),
                'gmv_product_card' => $this->number(
                    $row['gmv_dari_kartu_produk_afiliasi'] ?? 0
                ),

                'refund' => $this->number($row['pengembalian_dana'] ?? 0),

                'attributed_orders' => $this->integer(
                    $row['pesanan_teratribusi'] ?? 0
                ),

                'products_sold' => $this->integer(
                    $row['produk_yang_terjual_dari_kreator'] ?? 0
                ),

                'aov' => $this->number($row['aov'] ?? 0),

                'ctr' => $this->percentage($row['ctr'] ?? 0),
                'ctor' => $this->percentage($row['ctor'] ?? 0),

                'impressions' => $this->integer(
                    $row['impresi_produk'] ?? 0
                ),

                'video_views' => $this->integer(
                    $row['tayangan_video'] ?? 0
                ),

                'buyers' => $this->integer(
                    $row['pembeli'] ?? 0
                ),

                'commission' => $this->number(
                    $row['perkiraan_komisi'] ?? 0
                ),

                'live_count' => $this->integer(
                    $row['siaran_live'] ?? 0
                ),

                'video_count' => $this->integer(
                    $row['video'] ?? 0
                ),

                'showcase_products' => $this->integer(
                    $row['produk_yang_ditambahkan_ke_showcase'] ?? 0
                ),

                'content_samples' => $this->integer(
                    $row['jumlah_konten_sampel'] ?? 0
                ),

                'samples_sent' => $this->integer(
                    $row['sampel_terkirim'] ?? 0
                ),

                'products_returned' => $this->integer(
                    $row['produk_yang_dikembalikan_dananya'] ?? 0
                ),
            ]);
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }

    /**
     * Konversi angka biasa / format Indonesia.
     *
     * Contoh:
     * Rp212.616.764 -> 212616764
     * 212.616.764   -> 212616764
     * 212616764     -> 212616764
     * 134.482       -> 134482
     * 212.616.764,50 -> 212616764.50
     */
    private function number(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);

        $value = str_replace(
            ['Rp', 'rp', 'IDR', 'idr', ' '],
            '',
            $value
        );

        // Format Indonesia dengan titik sebagai pemisah ribuan
        // dan koma sebagai desimal.
        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (str_contains($value, '.')) {
            // Contoh: 212.616.764
            $value = str_replace('.', '', $value);
        } elseif (str_contains($value, ',')) {
            // Contoh: 212,50
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : 0;
    }

    /**
     * Konversi persentase Excel.
     *
     * Contoh:
     * 0.0089   -> 0.89
     * 0.0112   -> 1.12
     * "0.89%"  -> 0.89
     * "1,12%"  -> 1.12
     */
    private function percentage(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $hasPercentSymbol = false;

        if (!is_numeric($value)) {
            $value = trim((string) $value);

            $hasPercentSymbol = str_contains($value, '%');

            $value = str_replace(
                ['%', ' '],
                '',
                $value
            );

            if (str_contains($value, ',')) {
                $value = str_replace(',', '.', $value);
            }
        }

        if (!is_numeric($value)) {
            return 0;
        }

        $number = (float) $value;

        // Excel sering menyimpan 0,89% sebagai 0.0089
        if (!$hasPercentSymbol && $number > 0 && $number <= 1) {
            $number *= 100;
        }

        return $number;
    }

    private function integer(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $value = preg_replace(
            '/[^0-9-]/',
            '',
            (string) $value
        );

        return (int) $value;
    }
}