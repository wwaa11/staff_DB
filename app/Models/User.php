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
}
