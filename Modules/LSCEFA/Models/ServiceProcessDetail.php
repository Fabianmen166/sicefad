<?php

namespace Modules\LSCEFA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ServiceProcessDetail extends Model
{
    protected $fillable = [
        'process_id',
        'service_id',
        'status',
        'result',
        'file',
        'observations',
    ];

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id', 'process_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'services_id');
    }

    public function phAnalysis()
    {
        return $this->hasOne(PhAnalysis::class, 'analysis_id');
    }

    public function conductivityAnalysis()
    {
        return $this->hasOne(ConductivityAnalysis::class, 'analysis_id');
    }
    public function humidityAnalysis()
    {
        // Link only by process_id to be compatible with both schemas (with/without service_id)
        return $this->hasOne(\Modules\LSCEFA\Entities\HumidityAnalysis::class, 'process_id', 'process_id');
    }

    public function batchTextureAnalysis()
    {
        return $this->hasOne(\Modules\LSCEFA\Entities\BatchTextureAnalysis::class, 'process_id', 'process_id');
    }

}