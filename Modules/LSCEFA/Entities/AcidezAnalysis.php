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
    'error_analitico',
    'recuperacion',
    'valor_obtenido',
    'valor_referencia',
    'valor_obtenido',
    'consumido_muestra',
    'acidez',
    'observaciones',
    'status', // pending, approved, rejected
    'review_status',
    'review_observations',
    'reviewed_by',
    'review_date',
    'items_ensayo',
    'controles_analiticos',
    'muestra_referencia',
    'precision_analitica'
];

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id', 'process_id');
    }
    
    public function analyticalControl()
    {
        return $this->hasOne(AnalyticalControl::class, 'acidez_analysis_id');
    }
    
    public function reviewer()
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewed_by');
    }
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }
    public function serviceProcessDetails()
    {
        return $this->hasManyThrough(
            \Modules\LSCEFA\Models\ServiceProcessDetail::class,
            \Modules\LSCEFA\Models\Process::class,
            'process_id', // Clave foránea en processes
            'process_id', // Clave foránea en service_process_details
            'process_id', // Clave local en acidity_analyses
            'process_id'  // Clave local en processes
        );
    }   
}