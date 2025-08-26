<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PhosphorusAnalysis extends Model
{
    use HasFactory;

    protected $table = 'phosphorus_analyses';

    protected $fillable = [
        'process_id',
        'service_id',
        // English schema primary consecutive field
        'consecutive_no',
        'consecutivo_no',
        'fecha_analisis',
        'equipo_utilizado',
        'intervalo_metodo',
        'nombre_analista',
        'observaciones',
        'codigo_interno',
        'peso_muestra',
        'pw',
        'v_extractante',
        'lectura_blanco',
        'factor_dilucion',
        'fosforo_disponible_mg_l',
        'fosforo_disponible_mg_kg',
        'observaciones_item',
        // English fields (nullable)
        'analysis_date',
        'equipment_used',
        'method_interval',
        'analyst_name',
        'observations',
        'internal_code',
        'sample_weight',
        'extractant_volume',
        'blank_reading',
        'dilution_factor',
        'available_phosphorus_mg_l',
        'available_phosphorus_mg_kg',
        'item_observations',
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
        'available_phosphorus_mg_l' => 'decimal:4',
        'available_phosphorus_mg_kg' => 'decimal:4'
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
        return \Modules\LSCEFA\Database\factories\PhosphorusAnalysisFactory::new();
    }
} 