<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approver extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'userid',
        'level',
        'updated_userid',
        'updated_username',
    ];

    public function userData()
    {
        return $this->belongsTo(User::class, 'userid', 'userid');
    }

    public function email()
    {
        return $this->hasOne(Email::class, 'userid', 'userid');
    }
}
