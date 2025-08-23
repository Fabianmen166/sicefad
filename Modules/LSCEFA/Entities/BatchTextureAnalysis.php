<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchTextureAnalysis extends Model
{
    use HasFactory;

    protected $table = 'batch_texture_analyses';

    protected $fillable = [
        'consecutive_no',
        'analysis_date',
        'analyst_name',
        'methodology_used',
        'thermometer_code',
        'hydrometer_code',
        'equipment_used',
        'method_interval',
        'user_id',
        'process_id',
        'service_id',
        'samples',
        'analytical_controls',
        'duplicate_a_code',
        'duplicate_a_avg_sand',
        'duplicate_a_avg_clay',
        'duplicate_a_avg_silt',
        'duplicate_a_dpr_sand',
        'duplicate_a_dpr_clay',
        'duplicate_a_dpr_silt',
        'duplicate_a_acceptability',
        'duplicate_a_observations',
        'duplicate_b_code',
        'duplicate_b_avg_sand',
        'duplicate_b_avg_clay',
        'duplicate_b_avg_silt',
        'duplicate_b_dpr_sand',
        'duplicate_b_dpr_clay',
        'duplicate_b_dpr_silt',
        'duplicate_b_acceptability',
        'duplicate_b_observations',
        'reference_material_expected_sand',
        'reference_material_expected_clay',
        'reference_material_expected_silt',
        'reference_material_obtained_sand',
        'reference_material_obtained_clay',
        'reference_material_obtained_silt',
        'reference_material_error_percent',
        'reference_material_acceptability',
        'reference_material_observations',
        'general_observations',
        'extra_data',
        'review_status',
        'review_observations',
        'reviewed_by',
        'review_date',
    ];

    protected $casts = [
        'analysis_date' => 'date',
        'samples' => 'array',
        'analytical_controls' => 'array',
        'extra_data' => 'array',
        'review_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function analyticalControls()
    {
        return $this->hasMany(AnalyticalControl::class, 'analysis_id', 'id')
            ->where('analysis_type', 'texture');
    }

    public function process()
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\Process::class, 'process_id', 'process_id');
    }

    public function analysis()
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\ServiceProcessDetail::class, 'process_id', 'process_id');
    }
}
