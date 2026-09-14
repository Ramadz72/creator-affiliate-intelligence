<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorContent extends Model
{
    protected $table = 'creator_contents';

    protected $fillable = [
        'creator_id',
        'content_date',
        'content_url',
        'content_type',
        'views',
        'likes',
        'comments',
        'shares',
    ];

    protected $casts = [
        'content_date' => 'date',
        'views' => 'integer',
        'likes' => 'integer',
        'comments' => 'integer',
        'shares' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }
}