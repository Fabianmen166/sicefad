<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\LSCEFA\Models\Process;
use Illuminate\Support\Facades\Auth;

class HumidityAnalysis extends Model
{
    use HasFactory;

    protected $table = 'humidity_analyses';

    protected $fillable = [
        'process_id',
        'consecutivo_no',
        'fecha_analisis',
        'hora_ingreso_horno',
        'hora_salida_horno',
        'temperatura_horno',
        'nombre_metodo',
        'intervalo_metodo',
        'equipo_utilizado',
        'unidades_reporte_equipo',
        'resolucion_instrumental',
        'fecha_fin_analisis',
        'codigo_interno',
        'peso_capsula',
        'peso_muestra',
        'peso_capsula_muestra_humedad',
        'peso_capsula_muestra_seca',
        'porcentaje_humedad',
        'observaciones',
        'review_status',
        'reviewed_by',
        'reviewer_role',
        'review_date',
        'review_observations',
        'status'
    ];

    protected $casts = [
          'fecha_analisis' => 'date',
        'fecha_fin_analisis' => 'date',
        'review_date' => 'datetime',
        'peso_capsula' => 'decimal:4',
        'peso_muestra' => 'decimal:4',
        'peso_capsula_muestra_humedad' => 'decimal:4',
        'peso_capsula_muestra_seca' => 'decimal:4',
        'porcentaje_humedad' => 'decimal:2',
        'temperatura_horno' => 'decimal:2'
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class, 'process_id', 'process_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function analyticalControl(): HasOne
    {
        return $this->hasOne(AnalyticalControl::class, 'humidity_analysis_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePendingReview($query)
    {
        return $query->where('review_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('review_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('review_status', 'rejected');
    }
}