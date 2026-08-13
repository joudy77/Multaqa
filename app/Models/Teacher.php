<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    //
    protected $fillable = [
        'user_id',
        'college',
        'path',
        'days_of_memorization',
        'current_students',
        'students_limit',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
