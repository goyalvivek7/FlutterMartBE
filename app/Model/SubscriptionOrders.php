<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;

class SubscriptionOrders extends Model
{
    protected $table = 'subscription_orders';

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function delivery_address()
    {
        return $this->belongsTo(CustomerAddress::class, 'delivery_address_id');
    }

}
