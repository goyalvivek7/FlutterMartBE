<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $casts = [
        'user_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
