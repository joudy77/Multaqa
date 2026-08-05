<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmartRecitationSessionExcerpt extends Model
{
    protected $table = 'smart_recitation_session_excerpts';

    protected $fillable = [
        'session_id',
        'order_index',
        'from_word_id',
        'to_word_id',
        'from_page',
        'to_page',
        'from_line',
        'to_line',
        'score',
        'dominant_category',
        'category_breakdown',
    ];

    protected $casts = [
        'category_breakdown' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(RecitationSession::class, 'session_id');
    }
}
