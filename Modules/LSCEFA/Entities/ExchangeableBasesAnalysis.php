<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExchangeableBasesAnalysis extends Model
{
    use HasFactory;

    protected $table = 'exchangeable_bases_analyses';

    protected $fillable = [
        'process_id',
        'service_id',
        'analytical_control_id',
        'internal_code',
        'sample_weight',
        'moisture',
        'final_volume',
        'na_reading',
        'na_blank',
        'na_factor',
        'na_result',
        'k_reading',
        'k_blank',
        'k_factor',
        'k_result',
        'ca_reading',
        'ca_blank',
        'ca_factor',
        'ca_result',
        'mg_reading',
        'mg_blank',
        'mg_factor',
        'mg_result',
        'observations',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'sample_weight' => 'decimal:4',
        'moisture' => 'decimal:4',
        'final_volume' => 'decimal:2',
        'na_reading' => 'decimal:4',
        'na_blank' => 'decimal:4',
        'na_factor' => 'decimal:4',
        'na_result' => 'decimal:4',
        'k_reading' => 'decimal:4',
        'k_blank' => 'decimal:4',
        'k_factor' => 'decimal:4',
        'k_result' => 'decimal:4',
        'ca_reading' => 'decimal:4',
        'ca_blank' => 'decimal:4',
        'ca_factor' => 'decimal:4',
        'ca_result' => 'decimal:4',
        'mg_reading' => 'decimal:4',
        'mg_blank' => 'decimal:4',
        'mg_factor' => 'decimal:4',
        'mg_result' => 'decimal:4'
    ];

    public function process()
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\Process::class, 'process_id', 'process_id');
    }

    public function service()
    {
        return $this->belongsTo(\Modules\LSCEFA\Models\Service::class, 'service_id', 'services_id');
    }

    public function analyticalControl()
    {
        return $this->belongsTo(AnalyticalControl::class, 'analytical_control_id');
    }

    protected static function newFactory()
    {
        return \Modules\LSCEFA\Database\factories\ExchangeableBasesAnalysisFactory::new();
    }
} 