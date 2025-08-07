<?php

namespace Modules\LSCEFA\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\LSCEFA\Models\Quote;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Modules\LSCEFA\Entities\HumidityAnalysis;
use Modules\LSCEFA\Entities\CationicAnalysis;
use Modules\LSCEFA\Entities\PhosphorusAnalysis;

class Process extends Model
{
    protected $table = 'processes';
    protected $primaryKey = 'process_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'process_id',
        'quote_id',
        'item_code',
        'status',
        'client_communication',
        'communication_file',
        'processing_days',
        'reception_date',
        'description',
        'sampling_place',
        'sampling_date',
        'reception_responsible',
        'delivery_date',
    ];

    protected $casts = [
        'reception_date' => 'date',
        'sampling_date' => 'date',
        'delivery_date' => 'date',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'quote_id', 'quote_id');
    }

    public function serviceProcessDetails()
{
    return $this->hasMany(ServiceProcessDetail::class, 'process_id');
}

    // Relación con HumidityAnalysis
 public function analyses()
{
    return $this->hasMany(HumidityAnalysis::class, 'process_id', 'process_id');
}

    // Relación con CationicAnalysis
 public function cationicAnalyses()
{
    return $this->hasMany(CationicAnalysis::class, 'process_id', 'process_id');
}

    // Relación con PhosphorusAnalysis
 public function phosphorusAnalyses()
{
    return $this->hasMany(PhosphorusAnalysis::class, 'process_id', 'process_id');
}

    // Relación con el cliente a través de la cotización
    public function customer()
    {
        return $this->hasOneThrough(
            \Modules\LSCEFA\Models\Customer::class,
            Quote::class,
            'quote_id', // Clave foránea en quotes
            'customer_id', // Clave foránea en customers
            'quote_id', // Clave local en processes
            'customer_id' // Clave local en quotes
        );
    }

    public function service()
{
    return $this->belongsTo(Service::class, 'service_id');
}

} 