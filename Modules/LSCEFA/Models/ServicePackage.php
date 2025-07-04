<?php

namespace Modules\LSCEFA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServicePackage extends Model
{
    use HasFactory;

    protected $table = 'service_packages';
    protected $primaryKey = 'service_package_id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'name',
        'price',
        'accredited',
        'included_services',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'accredited' => 'boolean',
        'included_services' => 'array'
    ];

    public function getIncludedServicesAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function setIncludedServicesAttribute($value)
    {
        $this->attributes['included_services'] = is_array($value) ? json_encode($value) : $value;
    }

    // public function services()
    // {
    //     return $this->belongsToMany(Service::class, 'service_package_service', 'service_package_id', 'service_id');
    // }
} 