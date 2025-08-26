<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Entities\AnalyticalControl;
use App\Models\User;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;



class CarbonoAnalysis extends Model
{
    protected $table = 'carbono_analyses';

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
        'valor_cot_leido',
        'valor_leido',
        'porcentaje_humedad',
        'peso_muestra',
        'volumen_sulfato_blanco',
        'volumen_sulfato_muestra',
        'volumen_dicromato',
        'molaridad_sulfato',
        'porcentaje_co_total',
        'porcentaje_cot',
        'porcentaje_mo',
        'fortificado',
        'cot_muestra',
        'error_analitico',
        'observaciones',
        'review_status',
        'reviewed_by',
        'reviewer_role',
        'review_date',
        'review_observations',
    ];

    // Relación con Process
    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id', 'process_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function analyticalControl(): HasOne
    {
        return $this->hasOne(AnalyticalControl::class, 'carbono_analysis_id');
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
