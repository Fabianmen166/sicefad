<?php
namespace Modules\LSCEFA\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Quote extends Model
{
    protected $primaryKey = 'quote_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['quote_id', 'customer_id', 'user_id', 'total', 'file'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }
    public function quoteServices()
    {
        return $this->hasMany(QuoteService::class, 'quote_id', 'quote_id');
    }
    public function files()
    {
        return $this->hasMany(QuoteFile::class, 'quote_id');
    }
    public function processes()
    {
        return $this->hasMany(Process::class, 'quote_id', 'quote_id');
    }
} 