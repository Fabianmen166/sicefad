<?php

namespace Modules\LSCEFA\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'services';
    protected $primaryKey = 'services_id';
    public $timestamps = true;

    protected $fillable = [
        'descripcion',
        'precio',
        'acreditado'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'acreditado' => 'boolean'
    ];
} 