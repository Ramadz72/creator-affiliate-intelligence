<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affiliate extends Model
{
    protected $table = 'affiliates';

    protected $fillable = [
        'name',
        'username',
        'platform',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function performances(): HasMany
    {
        return $this->hasMany(AffiliatePerformance::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(AffiliateScore::class);
    }
}