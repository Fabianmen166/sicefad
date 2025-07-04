<?php

namespace Modules\LSCEFA\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteFile extends Model
{
    protected $fillable = [
        'quote_id',
        'filename',
        'path',
        'mime',
        'size',
    ];
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
} 