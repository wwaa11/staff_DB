<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'userid',
        'name',
        'name_EN',
        'position',
        'position_EN',
        'department',
        'password',
        'picture',
        'line_ID',
        'skip_hris',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department', 'id');
    }

    public function approver()
    {
        return $this->hasMany(Approver::class, 'department_id', 'department');
    }

    public function email()
    {
        return $this->hasOne(Email::class, 'userid', 'userid');
    }

    public function referance()
    {
        return $this->hasMany(Referance::class, 'userid', 'userid');
    }
}
