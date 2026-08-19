<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserApprover extends Model
{
    protected $fillable = [
        'userid',
        'approver_userid',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userid', 'userid');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_userid', 'userid');
    }
}
