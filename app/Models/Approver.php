<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approver extends Model
{
    use HasFactory;

    public function userData()
    {
        return $this->belongsTo(User::class, 'userid', 'userid');
    }

    public function email()
    {
        return $this->hasOne(Email::class, 'userid', 'userid');
    }
}
