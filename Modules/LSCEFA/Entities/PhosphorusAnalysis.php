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
        'consecutivo_no',
        'fecha_analisis',
        'unidades_reporte_equipo',
        'nombre_metodo',
        'equipo_utilizado',
        'intervalo_metodo',
        'nombre_analista',
        'resolucion_instrumental',
        'normalidad_naoh',
        'controles_analiticos',
        'precision_analitica',
        'veracidad_analitica',
        'muestra_referencia_certificada_analitica',
        'muestra_referencia_analitica',
        'items_ensayo',
        'observaciones',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'controles_analiticos' => 'array',
        'precision_analitica' => 'array',
        'veracidad_analitica' => 'array',
        'muestra_referencia_certificada_analitica' => 'array',
        'muestra_referencia_analitica' => 'array',
        'items_ensayo' => 'array',
        'fecha_analisis' => 'date',
        'normalidad_naoh' => 'decimal:4'
    ];

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id', 'process_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'services_id');
    }

    protected static function newFactory()
    {
        return \Modules\LSCEFA\Database\factories\PhosphorusAnalysisFactory::new();
    }
} 