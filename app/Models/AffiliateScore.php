<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateScore extends Model
{
    protected $table = 'affiliate_scores';

    public $timestamps = false;

    protected $fillable = [
        'affiliate_id',
        'import_batch_id',
        'performance_score',
        'growth_score',
        'consistency_score',
        'opportunity_score',
        'overall_score',
        'action',
        'generated_at',
    ];

    protected $casts = [
        'affiliate_id' => 'integer',
        'import_batch_id' => 'integer',

        'performance_score' => 'decimal:2',
        'growth_score' => 'decimal:2',
        'consistency_score' => 'decimal:2',
        'opportunity_score' => 'decimal:2',
        'overall_score' => 'decimal:2',

        'generated_at' => 'datetime',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }
}