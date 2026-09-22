<?php

namespace App\Models;
use App\Models\CreatorAnalysisSnapshot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CreatorScore extends Model
{
    protected $table = 'creator_scores';

    public $timestamps = false;

    protected $fillable = [
        'creator_id',
        'period_start',
        'period_end',
        'performance_score',
        'engagement_score',
        'audience_fit_score',
        'historical_score',
        'deal_value_score',
        'overall_score',
        'recommendation',
    ];

    protected $casts = [
        'creator_id' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
        'performance_score' => 'decimal:2',
        'engagement_score' => 'decimal:2',
        'audience_fit_score' => 'decimal:2',
        'historical_score' => 'decimal:2',
        'deal_value_score' => 'decimal:2',
        'overall_score' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    public function snapshot(): HasOne
    {
        return $this->hasOne(CreatorAnalysisSnapshot::class);
    }
}