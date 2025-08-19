<?php

namespace Modules\LSCEFA\Models;

use Illuminate\Database\Eloquent\Model;

class ConductivityAnalysis extends Model
{
    protected $table = 'conductivity_analyses';

    protected $fillable = [
        'analysis_id',
        'consecutivo_no',
        'nombre_metodo',
        'fecha_analisis',
        'user_id',
        'codigo_equipo',
        'serial_conductimetro',
        'serial_sonda_temperatura',
        'equipo_utilizado',
        'resolucion_instrumental',
        'unidades_reporte',
        'intervalo_metodo',
        'items_ensayo',
        'controles_analiticos',
        'precision_analitica',
        'veracidad_analitica',
        'observaciones',
        'revisado_por',
        'fecha_revision',
        'aprobado',
        'observaciones_revision',
        'review_status',
        'reviewed_by',
        'reviewer_role',
        'review_date',
        'review_observations',
    ];

    protected $casts = [
        'items_ensayo' => 'array',
        'controles_analiticos' => 'array',
        'precision_analitica' => 'array',
        'veracidad_analitica' => 'array',
        'fecha_analisis' => 'date',
        'fecha_revision' => 'date',
        'review_date' => 'datetime',
    ];

    public function analysis()
    {
        return $this->belongsTo(ServiceProcessDetail::class, 'analysis_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }
}