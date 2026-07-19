<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    //
    protected $fillable = [
        'user_id',
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
