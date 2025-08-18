<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\LSCEFA\Models\Process;

class AcidezAnalysis extends Model
{
    protected $table = 'acidity_analyses'; // Nombre correcto de la tabla
    
   protected $fillable = [
    'process_id',
    'consecutivo_no',
    'fecha_analisis',
    'unidades_reporte_equipo',
    'nombre_metodo',
    'equipo_utilizado',
    'intervalo_metodo',
    'resolucion_instrumental',
    'codigo_interno',
    'peso_muestra',
    'consumido_blanco',
    'porcentaje_humedad',
    'molaridad',
    'valor_referencia',
    'valor_obtenido',
    'consumido_muestra',
    'acidez',
    'observaciones',
    'status' // pending, approved, rejected
];

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id', 'process_id');
    }
    
    public function analyticalControl()
    {
        return $this->hasOne(AnalyticalControl::class, 'acidez_analysis_id');
    }
}