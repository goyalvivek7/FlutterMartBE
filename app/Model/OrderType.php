<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderType extends Model
{
    protected $table = 'delivery_options';
    protected $fillable = [
        'text', 'status'
    ];
}
