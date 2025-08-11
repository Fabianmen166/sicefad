<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TextureAnalysis extends Model
{
    use HasFactory;

    protected $table = 'texture_analyses';

    protected $fillable = [
        'process_id',
        'service_id',
        'consecutivo_no',
        'fecha_analisis',
        'equipo_utilizado',
        'intervalo_metodo',
        'analista',
        'user_id',
    ];

    protected $casts = [
        'fecha_analisis' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the process that owns the analysis.
     */
    public function process(): BelongsTo
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\Process::class, 'process_id', 'process_id');
    }

    /**
     * Get the service that owns the analysis.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\Service::class, 'service_id');
    }

    /**
     * Get the user that created the analysis.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the analysis items for this analysis.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TextureAnalysisItem::class, 'texture_analysis_id');
    }

    /**
     * Get the analytical controls for this analysis.
     */
    public function analyticalControls(): HasMany
    {
        return $this->hasMany(\Modules\LSCEFA\Entities\AnalyticalControl::class, 'analysis_id')
            ->where('analysis_type', 'texture');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Modules\LSCEFA\Database\factories\TextureAnalysisFactory::new();
    }
}
