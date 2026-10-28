<?php

namespace App\Notifications;

use App\Models\ImportBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ImportCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(public ImportBatch $batch) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $errorRows = (int) $this->batch->error_rows;
        $hasErrors = $errorRows > 0;

        return [
            'title' => $hasErrors
                ? 'Import selesai dengan catatan'
                : 'Import selesai',
            'message' => sprintf(
                'Import data %s selesai. %s baris berhasil, %s dilewati, %s error.',
                $this->batch->period_start->format('d/m/Y'),
                number_format((int) $this->batch->successful_rows, 0, ',', '.'),
                number_format((int) $this->batch->skipped_rows, 0, ',', '.'),
                number_format($errorRows, 0, ',', '.'),
            ),
            'href' => $this->batch->id
                ? route('history.show', $this->batch->id, false)
                : route('history.index', absolute: false),
            'category' => 'import',
            'severity' => $hasErrors ? 'warning' : 'success',
            'action' => 'Lihat detail',
            'meta' => [
                'import_batch_id' => $this->batch->id,
                'successful_rows' => (int) $this->batch->successful_rows,
                'skipped_rows' => (int) $this->batch->skipped_rows,
                'error_rows' => $errorRows,
            ],
        ];
    }
}
