<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingStudent extends Model
{
    use HasFactory;

    protected $table = 'pending_students';

    protected $fillable = [
        'id_number',
        'lastname',
        'firstname',
        'middle_initial',
        'birthday',
        'qrcode',
        'course',
        'year',
        'profile_picture',
        'student_signature',
    ];
    
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
