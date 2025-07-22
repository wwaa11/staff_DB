<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
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

    public function sign()
    {
        return $this->hasMany(Sign::class, 'userid', 'userid');
    }
}
