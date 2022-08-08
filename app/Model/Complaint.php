<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $table = 'complaints';
    protected $fillable = [
        'user_id', 'issue_id', 'comment', 'attachments', 'status', 'parent_id'
    ];
    public function complaint_issues()
    {
        return $this->belongsTo(ComplaintIssues::class, 'issue_id');
    }
    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
