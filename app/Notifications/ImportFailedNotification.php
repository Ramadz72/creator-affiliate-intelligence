<?php

namespace App\Notifications;

use App\Models\ImportBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ImportFailedNotification extends Notification
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
        return [
            'title' => 'Import gagal',
            'message' => sprintf(
                'Import data %s gagal: %s',
                $this->batch->period_start->format('d/m/Y'),
                Str::limit(
                    $this->batch->error_message ?: 'Terjadi kesalahan saat memproses data.',
                    160,
                ),
            ),
            'href' => $this->batch->id
                ? route('history.show', $this->batch->id, false)
                : route('history.index', absolute: false),
            'category' => 'import',
            'severity' => 'error',
            'action' => 'Lihat detail',
            'meta' => [
                'import_batch_id' => $this->batch->id,
                'successful_rows' => (int) $this->batch->successful_rows,
                'skipped_rows' => (int) $this->batch->skipped_rows,
                'error_rows' => (int) $this->batch->error_rows,
            ],
        ];
    }
}
