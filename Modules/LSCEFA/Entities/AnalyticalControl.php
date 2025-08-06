<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnalyticalControl extends Model
{
    use HasFactory;

    protected $table = 'analytical_controls';

    protected $fillable = [
        'process_id',
        'blank_identification',
        'blank_lcm',
        'blank_measured_value',
        'blank_acceptable',
        'blank_observations',
        'error_identification',
        'error_theoretical_value',
        'error_measured_value',
        'error_percentage',
        'error_acceptable',
        'error_observations',
        'recovery_identification',
        'recovery_theoretical_value',
        'recovery_measured_value',
        'recovery_percentage',
        'recovery_acceptable',
        'recovery_observations',
        'dpr_identification',
        'dpr_replicate1',
        'dpr_replicate2',
        'dpr_percentage',
        'dpr_acceptable',
        'dpr_observations',
        'analytical_controls',
        'dpr_duplicate_a',
        'dpr_duplicate_b',
        'dpr_result',
        'dpr_acceptability',
        'curve_measured_value',
        'curve_error_percentage',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'blank_lcm' => 'decimal:2',
        'blank_measured_value' => 'decimal:2',
        'error_theoretical_value' => 'decimal:2',
        'error_measured_value' => 'decimal:2',
        'error_percentage' => 'decimal:2',
        'recovery_theoretical_value' => 'decimal:2',
        'recovery_measured_value' => 'decimal:2',
        'recovery_percentage' => 'decimal:2',
        'dpr_replicate1' => 'decimal:2',
        'dpr_replicate2' => 'decimal:2',
        'dpr_percentage' => 'decimal:2',
        'analytical_controls' => 'array',
        'dpr_duplicate_a' => 'decimal:4',
        'dpr_duplicate_b' => 'decimal:4',
        'dpr_result' => 'decimal:4',
        'curve_measured_value' => 'decimal:4',
        'curve_error_percentage' => 'decimal:4'
    ];

    public function process()
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\Process::class, 'process_id', 'process_id');
    }

    protected static function newFactory()
    {
        return \Modules\LSCEFA\Database\factories\AnalyticalControlFactory::new();
    }
}