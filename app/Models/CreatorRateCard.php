<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorRateCard extends Model
{
    protected $table = 'creator_rate_cards';

    protected $fillable = [
        'creator_id',
        'platform',
        'deliverable',
        'price',
        'duration',
        'revision',
        'usage_rights',
        'valid_until',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'revision' => 'integer',
        'valid_until' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }
}