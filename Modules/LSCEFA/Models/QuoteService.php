<?php
namespace Modules\LSCEFA\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteService extends Model
{
    protected $fillable = [
        'quote_id',
        'service_id',
        'service_package_id',
        'quantity',
        'subtotal',
        'unit_index',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'quote_id', 'quote_id');
    }
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'services_id');
    }
    public function servicePackage()
    {
        return $this->belongsTo(ServicePackage::class, 'service_package_id', 'service_package_id');
    }
} 