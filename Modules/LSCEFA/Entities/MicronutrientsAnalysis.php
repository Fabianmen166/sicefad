<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MicronutrientsAnalysis extends Model
{
    use HasFactory;

    protected $table = 'micronutrients_analyses';

    protected $fillable = [
        'process_id',
        'service_id',
        'analytical_control_id',
        'internal_code',
        'sample_weight',
        'moisture',
        'final_volume',
        'zinc_reading',
        'zinc_blank',
        'zinc_factor',
        'zinc_result',
        'iron_reading',
        'iron_blank',
        'iron_factor',
        'iron_result',
        'manganese_reading',
        'manganese_blank',
        'manganese_factor',
        'manganese_result',
        'copper_reading',
        'copper_blank',
        'copper_factor',
        'copper_result',
        'boron_reading',
        'boron_blank',
        'boron_factor',
        'boron_result',
        'observations',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'sample_weight' => 'decimal:4',
        'moisture' => 'decimal:4',
        'final_volume' => 'decimal:2',
        'zinc_reading' => 'decimal:4',
        'zinc_blank' => 'decimal:4',
        'zinc_factor' => 'decimal:4',
        'zinc_result' => 'decimal:4',
        'iron_reading' => 'decimal:4',
        'iron_blank' => 'decimal:4',
        'iron_factor' => 'decimal:4',
        'iron_result' => 'decimal:4',
        'manganese_reading' => 'decimal:4',
        'manganese_blank' => 'decimal:4',
        'manganese_factor' => 'decimal:4',
        'manganese_result' => 'decimal:4',
        'copper_reading' => 'decimal:4',
        'copper_blank' => 'decimal:4',
        'copper_factor' => 'decimal:4',
        'copper_result' => 'decimal:4',
        'boron_reading' => 'decimal:4',
        'boron_blank' => 'decimal:4',
        'boron_factor' => 'decimal:4',
        'boron_result' => 'decimal:4'
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
        return $this->belongsTo(AnalyticalControl::class, 'analytical_control_id');
    }

    public function items()
    {
        return $this->hasMany(MicronutrientsAnalysisItem::class, 'analysis_id');
    }

    public function analyticalControls()
    {
        return $this->hasMany(AnalyticalControl::class, 'analysis_id')->where('analysis_type', 'micronutrients');
    }

    protected static function newFactory()
    {
        return \Modules\LSCEFA\Database\factories\MicronutrientsAnalysisFactory::new();
    }
}
