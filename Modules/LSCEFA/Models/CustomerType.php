<?php

namespace Modules\LSCEFA\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CustomerType extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'customer_types';
    protected $primaryKey = 'customer_type_id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'discount_percentage',
        'description'
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2'
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class, 'customer_type_id', 'customer_type_id');
    }
} 