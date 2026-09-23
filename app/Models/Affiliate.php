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

    public function performances(): HasMany
    {
        return $this->hasMany(AffiliatePerformance::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(AffiliateScore::class);
    }

    public function latestScore(): HasOne
    {
        return $this->hasOne(AffiliateScore::class)->latestOfMany('id');
    }

    public function latestPerformance(): HasOne
    {
        return $this->hasOne(AffiliatePerformance::class)->latestOfMany('id');
    }
}