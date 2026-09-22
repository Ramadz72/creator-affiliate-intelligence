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
        'uploaded_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'uploaded_by' => 'integer',
        'total_rows' => 'integer',
        'uploaded_at' => 'datetime',
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