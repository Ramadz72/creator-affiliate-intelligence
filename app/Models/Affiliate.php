<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Affiliate extends Model
{
    protected $table = 'affiliates';

    protected $fillable = [
        'user_id',
        'name',
        'username',
        'platform',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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

    /** @return HasOne<AffiliateScore, $this> */
    public function latestScore(): HasOne
    {
        return $this->hasOne(AffiliateScore::class)->latestOfMany('id');
    }

    /** @return HasOne<AffiliatePerformance, $this> */
    public function latestPerformance(): HasOne
    {
        return $this->hasOne(AffiliatePerformance::class)->latestOfMany('id');
    }
}
