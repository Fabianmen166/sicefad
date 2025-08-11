<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TextureAnalysisItem extends Model
{
    use HasFactory;

    protected $table = 'texture_analysis_items';

    protected $fillable = [
        'texture_analysis_id',
        'codigo_interno',
        'peso_arena',
        'peso_limo',
        'peso_arcilla',
        'peso_total',
        'porcentaje_arena',
        'porcentaje_limo',
        'porcentaje_arcilla',
        'clase_textural',
        'observaciones',
    ];

    protected $casts = [
        'peso_arena' => 'decimal:4',
        'peso_limo' => 'decimal:4',
        'peso_arcilla' => 'decimal:4',
        'peso_total' => 'decimal:4',
        'porcentaje_arena' => 'decimal:4',
        'porcentaje_limo' => 'decimal:4',
        'porcentaje_arcilla' => 'decimal:4',
    ];

    /**
     * Get the texture analysis that owns the item.
     */
    public function textureAnalysis(): BelongsTo
    {
        return $this->belongsTo(TextureAnalysis::class, 'texture_analysis_id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Modules\LSCEFA\Database\factories\TextureAnalysisItemFactory::new();
    }
}
