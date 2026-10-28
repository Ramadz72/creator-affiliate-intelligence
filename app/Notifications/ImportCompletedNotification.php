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
        return [
            'title' => 'Import selesai',
            'message' => sprintf(
                'Import data %s selesai. %s baris berhasil, %s dilewati, %s error.',
                $this->batch->period_start->format('d/m/Y'),
                number_format((int) $this->batch->successful_rows, 0, ',', '.'),
                number_format((int) $this->batch->skipped_rows, 0, ',', '.'),
                number_format((int) $this->batch->error_rows, 0, ',', '.'),
            ),
            'href' => $this->batch->id
                ? route('history.show', $this->batch->id, false)
                : route('history.index', absolute: false),
        ];
    }
}
