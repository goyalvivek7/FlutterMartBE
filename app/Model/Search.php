<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Search extends Model
{
	protected $table = 'searches';
    protected $casts = [
        'created_at'  => 'datetime',
    ];
}
