<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentEditRequest extends Model
{
    protected $fillable = [
        'student_id',
        'lastname',
        'firstname',
        'middle_initial',
        'birthday',
        'program_id',
        'year',
        'mobile_number',
        'address',
        'emergency_person',
        'emergency_relationship',
        'emergency_number',
        'emergency_address',
        'profile_picture',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
