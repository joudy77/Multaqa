<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecitationSession extends Model
{
    //
    protected $fillable = ['student_id', 'teacher_id', 'from_page', 'to_page', 'status', 'scheduled_date', 'reviewed_at', 'notes', 'is_smart_review'];

    protected $casts = [
        'is_smart_review' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function errors()
    {
        return $this->hasMany(RecitationError::class, 'session_id');
    }
}