<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\LSCEFA\Models\Process;
use Illuminate\Support\Facades\Auth;

class HumidityAnalysis extends Model
{
    use HasFactory;

    protected $table = 'humidity_analyses';

    protected $fillable = [
        'process_id',
        'service_id',
        'consecutive_no',
        'analysis_date',
        'user_id',
        'oven_entry_time',
        'oven_exit_time',
        'oven_temperature',
        'method_name',
        'method_interval',
        'equipment_used',
        'equipment_report_units',
        'instrumental_resolution',
        'analysis_end_date',
        'internal_code',
        'capsule_weight',
        'wet_weight',
        'capsule_sample_wet_weight',
        'capsule_sample_dry_weight',
        'dry_weight',
        'moisture',
        'observations',
        'review_status',
        'reviewed_by',
        'reviewer_role',
        'review_date',
        'review_observations',
    ];

    protected $casts = [
        'analysis_date' => 'date',
        'oven_entry_time' => 'datetime:H:i',
        'oven_exit_time' => 'datetime:H:i',
        'review_date' => 'datetime',
        'capsule_weight' => 'decimal:3',
        'wet_weight' => 'decimal:3',
        'dry_weight' => 'decimal:3',
        'moisture' => 'decimal:2',
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class, 'process_id', 'process_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function analyticalControl(): HasOne
    {
        return $this->hasOne(AnalyticalControl::class, 'humidity_analysis_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePendingReview($query)
    {
        return $query->where('review_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('review_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('review_status', 'rejected');
    }
}