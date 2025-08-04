<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\LSCEFA\Models\Process;

class AnalyticalControl extends Model
{
    use HasFactory;

    protected $table = 'analytical_controls';

    protected $fillable = [
        'process_id', // Añadido para relación directa con Process
        'analysis_id', // Cambiado de analysis_id para consistencia
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
        'dpr',
        'identificacion_mf',
        'identificacion_mr',
        'identificacion_dm',
        'identificacion_bm',
        'replica_1',
        'replica_2',
        'estado',
        'observaciones'
    ];

    // Relación con Process (si es necesaria)
    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id', 'process_id');
    }

    // Relación con HumidityAnalysis (correcta)
    public function humidityAnalysis()
    {
        return $this->belongsTo(HumidityAnalysis::class, 'humidity_analysis_id');
    }
    public function carbonoAnalysis()
    {
        return $this->belongsTo(CarbonoAnalysis::class, 'carbono_analysis_id');
    }
    
    
}