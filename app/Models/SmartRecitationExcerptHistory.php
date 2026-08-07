<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmartRecitationExcerptHistory extends Model
{
    protected $table = 'smart_recitation_excerpt_history';

    protected $fillable = ['student_id', 'from_word_id', 'to_word_id', 'suggested_at'];

    public $timestamps = false;

    protected $casts = [
        'suggested_at' => 'datetime',
    ];
}
