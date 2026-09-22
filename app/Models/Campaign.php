<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'creator_id',
        'campaign_name',
        'product_name',
        'platform',
        'deliverable',
        'agreed_price',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'agreed_price' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    public function performances(): HasMany
    {
        return $this->hasMany(CreatorCampaignPerformance::class);
    }
}