<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BoronAnalysisDetail extends Model
{
    use HasFactory;

    protected $table = 'boron_analysis_details';

    protected $fillable = [
        'process_id',
        'service_id',
        'consecutive_no',
        'applied_methodology',
        'method_interval',
        'analysis_date',
        'equipment_used',
        'analyst_name',
        
        // Controles analíticos - Estándar A
        'standard_a_identification',
        'standard_a_expected_value',
        'standard_a_read_value',
        'standard_a_error_percentage',
        'standard_a_error_acceptability',
        'standard_a_recovery_percentage',
        'standard_a_recovery_acceptability',
        'standard_a_dpr_percentage',
        'standard_a_dpr_acceptability',
        
        // Controles analíticos - Estándar B
        'standard_b_identification',
        'standard_b_expected_value',
        'standard_b_read_value',
        'standard_b_error_percentage',
        'standard_b_error_acceptability',
        'standard_b_recovery_percentage',
        'standard_b_recovery_acceptability',
        'standard_b_dpr_percentage',
        'standard_b_dpr_acceptability',
        
        // Curva de calibración
        'calibration_curve_value',
        'calibration_curve_read_value',
        'calibration_curve_error_percentage',
        'calibration_curve_acceptability',
        
        // Duplicados
        'duplicate_a_value',
        'duplicate_b_value',
        'duplicate_dpr_percentage',
        'duplicate_dpr_acceptability',
        
        // Items de ensayo
        'test_items',
        
        // Observaciones generales
        'general_observations'
    ];

    protected $casts = [
        'analysis_date' => 'date',
        'standard_a_expected_value' => 'decimal:4',
        'standard_a_read_value' => 'decimal:4',
        'standard_a_error_percentage' => 'decimal:2',
        'standard_a_recovery_percentage' => 'decimal:2',
        'standard_a_dpr_percentage' => 'decimal:2',
        'standard_b_expected_value' => 'decimal:4',
        'standard_b_read_value' => 'decimal:4',
        'standard_b_error_percentage' => 'decimal:2',
        'standard_b_recovery_percentage' => 'decimal:2',
        'standard_b_dpr_percentage' => 'decimal:2',
        'calibration_curve_value' => 'decimal:4',
        'calibration_curve_read_value' => 'decimal:4',
        'calibration_curve_error_percentage' => 'decimal:2',
        'duplicate_a_value' => 'decimal:4',
        'duplicate_b_value' => 'decimal:4',
        'duplicate_dpr_percentage' => 'decimal:2',
        'test_items' => 'array'
    ];

    public function process()
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\Process::class, 'process_id', 'process_id');
    }

    public function service()
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\Service::class, 'service_id', 'services_id');
    }

    protected static function newFactory()
    {
        return \Modules\LSCEFA\Database\factories\BoronAnalysisDetailFactory::new();
    }
}
