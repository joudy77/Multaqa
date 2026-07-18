<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    //
    protected $fillable = [
        'last_name',
        'mother_name',
        'father_name',
        'home_address',
        'goal',
        'achievement',
        'college',
        'path',
        'user_id',
        'teacher_id',
        'start_page',
        'end_page', 
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
