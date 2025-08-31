<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SulfurAnalysis extends Model
{
    use HasFactory;

    protected $table = 'sulfur_analyses';

    protected $fillable = [
        'process_id',
        'service_id',
        'consecutive_no',
        'analysis_date',
        'equipment_used',
        'method_interval',
        'analyst_name',
        'observations',
        'internal_code',
        'sample_weight',
        'pw',
        'extractant_volume',
        'blank_reading',
        'dilution_factor',
        'available_sulfur_mg_l',
        'available_sulfur_mg_kg',
        'item_observations',
        'review_status',
        'review_observations',
        'reviewed_by',
        'review_date',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'analysis_date' => 'date',
        'sample_weight' => 'decimal:4',
        'pw' => 'decimal:4',
        'extractant_volume' => 'decimal:2',
        'blank_reading' => 'decimal:4',
        'dilution_factor' => 'decimal:4',
        'available_sulfur_mg_l' => 'decimal:4',
        'available_sulfur_mg_kg' => 'decimal:4'
    ];

    public function process()
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\Process::class, 'process_id', 'process_id');
    }

    public function service()
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\Service::class, 'service_id', 'services_id');
    }

    public function analyticalControl()
    {
        return $this->hasOne(\Modules\LSCEFA\Entities\AnalyticalControl::class, 'process_id', 'process_id');
    }

    protected static function newFactory()
    {
        return \Modules\LSCEFA\Database\factories\SulfurAnalysisFactory::new();
    }
}
