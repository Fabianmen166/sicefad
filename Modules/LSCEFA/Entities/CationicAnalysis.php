<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\LSCEFA\Models\Process;
use Illuminate\Support\Facades\Auth;

class CationicAnalysis extends Model
{
    use HasFactory;

    protected $table = 'cationic_analyses';

    protected $fillable = [
        'process_id', // Añadido para relación directa con Process
        'service_id', // Añadido para relación directa con Service
        'consecutivo_no',
        'fecha_analisis',
        'user_id',
        'hora_inicio',
        'hora_fin',
        'temperatura_laboratorio',
        'nombre_metodo',
        'intervalo_metodo',
        'equipo_utilizado',
        'unidades_reporte_equipo',
        'resolucion_instrumental',
        'fecha_fin_analisis',
        'codigo_interno',
        'peso_muestra',
        'volumen_extractante',
        'concentracion_nh4oac',
        'ph_extractante',
        'temperatura_extraccion',
        'tiempo_agitation',
        'concentracion_calcio',
        'concentracion_magnesio',
        'concentracion_sodio',
        'concentracion_potasio',
        'capacidad_intercambio_cationico',
        'observaciones',
        'review_status',
        'reviewed_by',
        'reviewer_role',
        'review_date',
        'review_observations',
    ];

    protected $casts = [
        'fecha_analisis' => 'date',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
        'review_date' => 'datetime',
        'peso_muestra' => 'decimal:4',
        'volumen_extractante' => 'decimal:2',
        'concentracion_nh4oac' => 'decimal:2',
        'ph_extractante' => 'decimal:2',
        'temperatura_extraccion' => 'decimal:2',
        'tiempo_agitation' => 'decimal:2',
        'concentracion_calcio' => 'decimal:2',
        'concentracion_magnesio' => 'decimal:2',
        'concentracion_sodio' => 'decimal:2',
        'concentracion_potasio' => 'decimal:2',
        'capacidad_intercambio_cationico' => 'decimal:2',
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
        return $this->hasOne(AnalyticalControl::class, 'cationic_analysis_id');
    }

    /**
     * Relación con el revisor
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Relación con el servicio
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\Service::class, 'service_id');
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

    /**
     * Calcular la suma de cationes intercambiables
     */
    public function getSumaCationesAttribute()
    {
        return $this->concentracion_calcio + 
               $this->concentracion_magnesio + 
               $this->concentracion_sodio + 
               $this->concentracion_potasio;
    }

    /**
     * Calcular el porcentaje de saturación de cada catión
     */
    public function getPorcentajeCalcioAttribute()
    {
        if ($this->suma_cationes > 0) {
            return ($this->concentracion_calcio / $this->suma_cationes) * 100;
        }
        return 0;
    }

    public function getPorcentajeMagnesioAttribute()
    {
        if ($this->suma_cationes > 0) {
            return ($this->concentracion_magnesio / $this->suma_cationes) * 100;
        }
        return 0;
    }

    public function getPorcentajeSodioAttribute()
    {
        if ($this->suma_cationes > 0) {
            return ($this->concentracion_sodio / $this->suma_cationes) * 100;
        }
        return 0;
    }

    public function getPorcentajePotasioAttribute()
    {
        if ($this->suma_cationes > 0) {
            return ($this->concentracion_potasio / $this->suma_cationes) * 100;
        }
        return 0;
    }
} 