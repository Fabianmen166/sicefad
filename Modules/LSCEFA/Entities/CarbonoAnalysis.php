<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarbonoAnalysis extends Model
{
    use HasFactory;

     protected $fillable = [
        'analysis_id',
        'consecutivo_no',
        'fecha_analisis',
        'user_id',
        'nombre_metodo',
        'unidades_reporte_equipo',
        'equipo_utilizado',
        'intervalo_metodo',
        'resolucion_instrumental',
        'codigo_interno',
        'valor_cot_leido',
        'valor_leido',
        'porcentaje_humedad',
        'volumen_sulfato_blanco',
        'volumen_sulfato_muestra',
        'volumen_dicromato',
        'molaridad_sulfato',
        'porcentaje_co_total',
        'porcentaje_cot',
        'porcentaje_mo',
        'fortificado',
        'peso_muestra',
        'cot_muestra',
        'error_analitico',
        'observaciones',
     
        
    ];
    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class, 'process_id', 'process_id');
    }

     public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    

    
    protected static function newFactory()
    {
        return \Modules\LSCEFA\Database\factories\CarbonoAnalysisFactory::new();
    }
    
    public function analyticalControl(): HasOne
    {
        return $this->hasOne(AnalyticalControl::class, 'humidity_analysis_id');
    }
    
}
