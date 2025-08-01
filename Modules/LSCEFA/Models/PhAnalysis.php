<?php

namespace Modules\LSCEFA\Models;

use Illuminate\Database\Eloquent\Model;

class PhAnalysis extends Model
{
    protected $fillable = [
        'analysis_id',
        'consecutivo_no',
        'fecha_analisis',
        'user_id',
        'codigo_probeta',
        'codigo_equipo',
        'serial_electrodo',
        'serial_sonda_temperatura',
        'controles_analiticos',
        'precision_analitica',
        'items_ensayo',
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
        'controles_analiticos' => 'array',
        'precision_analitica' => 'array',
        'items_ensayo' => 'array',
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