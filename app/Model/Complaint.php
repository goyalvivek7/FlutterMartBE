<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $table = 'complaints';
    protected $fillable = [
        'user_id', 'issue_id', 'comment', 'attachments', 'status', 'parent_id'
    ];
}
