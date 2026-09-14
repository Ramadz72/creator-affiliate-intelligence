<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliatePerformance extends Model
{
    protected $table = 'affiliate_performances';

    protected $fillable = [
        'affiliate_id',
        'import_batch_id',
        'gmv',
        'gmv_live',
        'gmv_video',
        'gmv_product_card',
        'refund',
        'attributed_orders',
        'products_sold',
        'aov',
        'ctr',
        'ctor',
        'impressions',
        'video_views',
        'buyers',
        'commission',
        'live_count',
        'video_count',
        'showcase_products',
        'content_samples',
        'samples_sent',
        'products_returned',
        'notes',
    ];

    protected $casts = [
        'affiliate_id' => 'integer',
        'import_batch_id' => 'integer',

        'gmv' => 'decimal:2',
        'gmv_live' => 'decimal:2',
        'gmv_video' => 'decimal:2',
        'gmv_product_card' => 'decimal:2',
        'refund' => 'decimal:2',

        'attributed_orders' => 'integer',
        'products_sold' => 'integer',

        'aov' => 'decimal:2',
        'ctr' => 'decimal:2',
        'ctor' => 'decimal:2',

        'impressions' => 'integer',
        'video_views' => 'integer',
        'buyers' => 'integer',

        'commission' => 'decimal:2',

        'live_count' => 'integer',
        'video_count' => 'integer',
        'showcase_products' => 'integer',
        'content_samples' => 'integer',
        'samples_sent' => 'integer',
        'products_returned' => 'integer',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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