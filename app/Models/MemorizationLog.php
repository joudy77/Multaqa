<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemorizationLog extends Model
{
    //
    protected $fillable = [
        'student_id',
        'teacher_id',
        'parts',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
