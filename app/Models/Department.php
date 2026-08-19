<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'department',
        'department_EN',
        'division',
        'division_EN',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'department', 'id');
    }

    public function approvers()
    {
        return $this->hasMany(Approver::class, 'department_id', 'id');
    }
}
