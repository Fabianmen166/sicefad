<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExchangeableBasesAnalysis extends Model
{
    use HasFactory;

    protected $table = 'exchangeable_bases_analyses';

    protected $fillable = [
        'process_id',
        'service_id',
        'analytical_control_id',
        // Campos definidos en la migración (español)
        'codigo_interno',
        'peso_muestra',
        'pw',
        'v_extractante',
        'lectura_blanco',
        'factor_dilucion',
        'bases_cambiables_mg_l',
        'bases_cambiables_mg_kg',
        'observaciones_item',
        // Cationes
        'na_blank', 'na_factor', 'na_result',
        'k_blank', 'k_factor', 'k_result',
        'ca_blank', 'ca_factor', 'ca_result',
        'mg_blank', 'mg_factor', 'mg_result',
        'created_at', 'updated_at'
    ];

    protected $casts = [
        'peso_muestra' => 'decimal:4',
        'pw' => 'decimal:4',
        'v_extractante' => 'decimal:2',
        'lectura_blanco' => 'decimal:4',
        'factor_dilucion' => 'decimal:4',
        'bases_cambiables_mg_l' => 'decimal:4',
        'bases_cambiables_mg_kg' => 'decimal:4',
        'na_blank' => 'decimal:4', 'na_factor' => 'decimal:4', 'na_result' => 'decimal:4',
        'k_blank' => 'decimal:4', 'k_factor' => 'decimal:4', 'k_result' => 'decimal:4',
        'ca_blank' => 'decimal:4', 'ca_factor' => 'decimal:4', 'ca_result' => 'decimal:4',
        'mg_blank' => 'decimal:4', 'mg_factor' => 'decimal:4', 'mg_result' => 'decimal:4'
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

    protected static function newFactory()
    {
        return \Modules\LSCEFA\Database\factories\ExchangeableBasesAnalysisFactory::new();
    }
} 