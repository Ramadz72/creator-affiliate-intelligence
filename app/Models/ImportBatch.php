<?php

namespace App\Models;

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

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** @return HasMany<AffiliatePerformance, $this> */
    public function performances(): HasMany
    {
        return $this->hasMany(AffiliatePerformance::class);
    }

    /** @return HasMany<AffiliateScore, $this> */
    public function scores(): HasMany
    {
        return $this->hasMany(AffiliateScore::class);
    }
}
