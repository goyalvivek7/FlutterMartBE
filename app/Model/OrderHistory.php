<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    protected $table = 'order_histories';
    protected $fillable = [
        'order_id', 'user_id', 'user_type', 'status_captured', 'status_reason', 'signature','reason_id'
    ];
}
