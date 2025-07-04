<?php

namespace Modules\LSCEFA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';
    protected $primaryKey = 'customer_id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'applicant',
        'contact',
        'phone',
        'tax_id',
        'email',
        'customer_type_id',
    ];

    public function customerType()
    {
        return $this->belongsTo(CustomerType::class, 'customer_type_id', 'customer_type_id');
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class, 'customer_id', 'customer_id');
    }
} 