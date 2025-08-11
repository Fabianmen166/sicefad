<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CationicAnalysis extends Model
{
    use HasFactory;

    protected $table = 'cationic_analyses';

    protected $fillable = [
        'process_id',
        'consecutivo_no',
        'fecha_analisis',
        'hora_inicio',
        'hora_fin',
        'temperatura_laboratorio',
        'nombre_metodo',
        'intervalo_metodo',
        'equipo_utilizado',
        'unidades_reporte_equipo',
        'resolucion_instrumental',
        'fecha_fin_analisis',
        'nombre_analista',
        'peso_muestra',
        'vol_naoh_muestra',
        'vol_naoh_blanco',
        'normalidad_naoh',
        'humedad_porcentaje',
        'cic_resultado',
        'concentracion_calcio',
        'concentracion_magnesio',
        'concentracion_sodio',
        'concentracion_potasio',
        'capacidad_intercambio_cationico',
        'observaciones',
        'review_status',
        'reviewed_by',
        'reviewer_role',
        'review_date',
        'review_observations',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'fecha_analisis' => 'date',
        'fecha_fin_analisis' => 'date',
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime',
        'temperatura_laboratorio' => 'decimal:2',
        'peso_muestra' => 'decimal:4',
        'vol_naoh_muestra' => 'decimal:2',
        'vol_naoh_blanco' => 'decimal:2',
        'normalidad_naoh' => 'decimal:2',
        'humedad_porcentaje' => 'decimal:2',
        'cic_resultado' => 'decimal:2',
        'concentracion_calcio' => 'decimal:2',
        'concentracion_magnesio' => 'decimal:2',
        'concentracion_sodio' => 'decimal:2',
        'concentracion_potasio' => 'decimal:2',
        'capacidad_intercambio_cationico' => 'decimal:2',
        'review_date' => 'datetime'
    ];

    public function process()
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\Process::class, 'process_id', 'process_id');
    }

    public function service()
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\Service::class, 'service_id', 'services_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewed_by');
    }

    protected static function newFactory()
    {
        return \Modules\LSCEFA\Database\factories\CationicAnalysisFactory::new();
    }
} 