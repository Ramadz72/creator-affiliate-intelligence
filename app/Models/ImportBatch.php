<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    protected $table = 'import_batches';

    const UPDATED_AT = null;

    protected $fillable = [
        'file_name',
        'period_start',
        'period_end',
        'uploaded_by',
        'status',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'skipped_rows',
        'error_rows',
        'progress',
        'uploaded_at',
        'started_at',
        'completed_at',
        'error_message',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'uploaded_by' => 'integer',
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'successful_rows' => 'integer',
        'skipped_rows' => 'integer',
        'error_rows' => 'integer',
        'progress' => 'integer',
        'uploaded_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function performances(): HasMany
    {
        return $this->hasMany(AffiliatePerformance::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(AffiliateScore::class);
    }
}