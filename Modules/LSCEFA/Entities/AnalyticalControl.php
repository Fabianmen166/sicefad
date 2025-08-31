<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnalyticalControl extends Model
{
    use HasFactory;

    protected $table = 'analytical_controls';

    protected $fillable = [
        // Claves y enlaces
        'process_id',
        'analysis_type',
        'analysis_id',
        'humidity_analysis_id',

        'masa_suelo',
        'masa_agua',
        'masa_suelo_seco',
        'humedad_fortificada_teorica',
        'humedad_obtenida',
        'humedad_fortificada',
        'recuperacion',
        'valor_referencia',
        'valor_obtenido',
        'valor_leido',
        'blanco_metodo',
        'resultado',
        'limite_cuantificacion_metodo',
        'rango_metodo',
        'humedad_replica_1',
        'humedad_replica_2',
        'replica_1',
        'replica_2',
        'dpr',
        'identificacion_mf',
        'identificacion_mr',
        'identificacion_dm',
        'identificacion_bm',
        'estado',
        'observaciones',

        // Campos de blanco (intercambio catiónico)
        'blanco_identificacion',
        'blanco_lcm',
        'blanco_valor_leido',
        'blanco_aceptable',
        'blanco_observaciones',

        // Campos de error (intercambio catiónico)
        'error_identificacion',
        'error_valor_teorico',
        'error_valor_leido',
        'error_porcentaje',
        'error_aceptable',
        'error_observaciones',

        // Campos de recuperación
        'recuperacion_identificacion',
        'recuperacion_valor_teorico',
        'recuperacion_valor_leido',
        'recuperacion_porcentaje',
        'recuperacion_aceptable',
        'recuperacion_observaciones',

        // Campos de DPR
        'dpr_identificacion',
        'dpr_replica1',
        'dpr_replica2',
        'dpr_porcentaje',
        'dpr_aceptable',
        'dpr_observaciones',
        
        // Campos JSON
        'controles_analiticos',
        
        // Campos DPR de curva
        'dpr_duplicado_a',
        'dpr_duplicado_b',
        'dpr_resultado',
        'dpr_aceptabilidad',
        
        // Campos de curva de calibración
        'curva_valor_leido',
        'curva_error_porcentaje',

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
        'dpr' => 'decimal:2',
        'replica_1' => 'decimal:2',
        'replica_2' => 'decimal:2',
        'dpr_replicate2' => 'decimal:2',
        'dpr_percentage' => 'decimal:2',
        'dpr_duplicate_a' => 'decimal:4',
        'dpr_duplicate_b' => 'decimal:4',
        'dpr_result' => 'decimal:4',
        'curve_measured_value' => 'decimal:4',
        'curve_error_percentage' => 'decimal:4',

        // Campos específicos para intercambio catiónico
        'blanco_lcm' => 'decimal:2',
        'blanco_valor_leido' => 'decimal:2',
        'error_valor_teorico' => 'decimal:2',
        'error_valor_leido' => 'decimal:2',
        'error_porcentaje' => 'decimal:2',
        'recuperacion_valor_teorico' => 'decimal:2',
        'recuperacion_valor_leido' => 'decimal:2',
        'recuperacion_porcentaje' => 'decimal:2',
        'dpr_replica1' => 'decimal:2',
        'dpr_replica2' => 'decimal:2',
        'dpr_porcentaje' => 'decimal:2',

        // JSON
        'controles_analiticos' => 'array',
        'analytical_controls' => 'array',
    ];

    public function process()
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\Process::class, 'process_id', 'process_id');
    }

    public function humidityAnalysis()
    {
        return $this->belongsTo(\Modules\LSCEFA\Entities\HumidityAnalysis::class, 'humidity_analysis_id');
    }

    public function serviceProcessDetail()
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\ServiceProcessDetail::class, 'analysis_id');
    }

    protected static function newFactory()
    {
        return \Modules\LSCEFA\Database\factories\AnalyticalControlFactory::new();
    }

    
    
    
}