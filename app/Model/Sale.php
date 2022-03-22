<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $table = 'sales';

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }
}
