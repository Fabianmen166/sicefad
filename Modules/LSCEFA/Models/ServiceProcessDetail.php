<?php

namespace Modules\LSCEFA\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceProcessDetail extends Model
{
    protected $table = 'service_process_details';
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
} 