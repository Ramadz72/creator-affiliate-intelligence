<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorCampaignPerformance extends Model
{
    protected $table = 'creator_campaign_performances';

    // Tabel hanya memiliki created_at, tidak memiliki updated_at
    public $timestamps = false;

    protected $fillable = [
        'campaign_id',
        'views',
        'likes',
        'comments',
        'shares',
        'saves',
        'clicks',
        'orders',
        'buyers',
        'gmv',
        'engagement_rate',
        'conversion_rate',
        'cost_per_view',
        'cost_per_order',
        'roi',
    ];

    protected $casts = [
        'campaign_id' => 'integer',
        'views' => 'integer',
        'likes' => 'integer',
        'comments' => 'integer',
        'shares' => 'integer',
        'saves' => 'integer',
        'clicks' => 'integer',
        'orders' => 'integer',
        'buyers' => 'integer',
        'gmv' => 'decimal:2',
        'engagement_rate' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
        'cost_per_view' => 'decimal:2',
        'cost_per_order' => 'decimal:2',
        'roi' => 'decimal:2',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}