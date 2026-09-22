<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorAnalysisSnapshot extends Model
{
    protected $table = 'creator_analysis_snapshots';

    public $timestamps = false;

    protected $fillable = [
        'creator_score_id',
        'content_count',
        'average_views',
        'engagement_rate',
        'rate_card_platform',
        'rate_card_deliverable',
        'rate_card_price',
        'cost_per_view',
        'insights',
        'created_at',
    ];

    protected $casts = [
        'average_views' => 'integer',
        'engagement_rate' => 'decimal:4',
        'rate_card_price' => 'decimal:2',
        'cost_per_view' => 'decimal:8',
        'insights' => 'array',
        'created_at' => 'datetime',
    ];

    public function creatorScore(): BelongsTo
    {
        return $this->belongsTo(CreatorScore::class);
    }

    
}