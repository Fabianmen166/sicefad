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
        'process_id', // Añadido para relación directa con Process
        'service_id', // Añadido para relación directa con Service
        'consecutivo_no',
        'fecha_analisis',
        'user_id',
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
    ];

    protected $casts = [
        'fecha_analisis' => 'date',
        'hora_ingreso_horno' => 'datetime:H:i',
        'hora_salida_horno' => 'datetime:H:i',
        'review_date' => 'datetime',
        'peso_capsula' => 'decimal:3',
        'peso_muestra' => 'decimal:3',
        'porcentaje_humedad' => 'decimal:2',
    ];

    /**
     * Relación con el proceso padre
     */
    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class, 'process_id', 'process_id');
    }

    /**
     * Relación con el usuario que realizó el análisis
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el control analítico
     */
    public function analyticalControl(): HasOne
    {
        return $this->hasOne(AnalyticalControl::class, 'humidity_analysis_id');
    }

    /**
     * Relación con el revisor
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes...

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