<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MicronutrientsAnalysis extends Model
{
    use HasFactory;

    protected $table = 'micronutrients_analyses';

    protected $fillable = [
        'analysis_id',
        'consecutivo_no',
        'fecha_analisis',
        'user_id',
        'equipo_utilizado',
        'intervalo_metodo',
        'controles_analiticos',
        'precision_analitica',
        'veracidad_analitica',
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
        'fecha_analisis' => 'date',
        'fecha_revision' => 'date',
        'review_date' => 'datetime',
        'controles_analiticos' => 'array',
        'precision_analitica' => 'array',
        'veracidad_analitica' => 'array',
        'items_ensayo' => 'array',
    ];

    public function process()
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\Process::class, 'process_id', 'process_id');
    }

    public function service()
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\Service::class, 'service_id', 'services_id');
    }

    public function analysis()
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\ServiceProcessDetail::class, 'analysis_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    protected static function newFactory()
    {
        return \Modules\LSCEFA\Database\factories\MicronutrientsAnalysisFactory::new();
    }
}
