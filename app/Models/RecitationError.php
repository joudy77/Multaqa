<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecitationError extends Model
{
    protected $fillable = ['session_id', 'student_id', 'word_id', 'surah_number', 'ayah_number', 'error_type', 'mawdi_id'];

    public function mawdi()
    {
        return $this->belongsTo(Mawdi::class, 'mawdi_id', 'mawdi_id');
    }
}