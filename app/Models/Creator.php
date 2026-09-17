<?php

namespace App\Models;
use App\Models\CreatorContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Creator extends Model
{
    protected $table = 'creators';

    protected $fillable = [
        'name',
        'username',
        'platform',
        'category',
        'followers',
        'audience_gender',
        'audience_age',
        'audience_location',
        'profile_link',
        'profile_image',
        'status',
        'notes',
    ];

    protected $casts = [
        'followers' => 'integer',
        'audience_gender' => 'array',
        'audience_age' => 'array',
        'audience_location' => 'array',
    ];

    public function contents(): HasMany
    {
        return $this->hasMany(CreatorContent::class);
    }

    public function rateCards(): HasMany
    {
        return $this->hasMany(CreatorRateCard::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(CreatorScore::class);
    }
}