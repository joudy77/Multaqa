<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * جدول word_colors — ناتج مرحلة الـ OCR: لكل كلمة (word_id مطابق
 * لـ quran_words.id) هل هي "حمرا" (is_red) وإذا إي، لأي موضع
 * (mawdi_id) بكتاب التبيان بتنتمي.
 */
class WordColor extends Model
{
    protected $table = 'word_colors';
    protected $primaryKey = 'word_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'word_id', 'surah', 'ayah', 'position', 'word_text',
        'is_red', 'mawdi_id', 'segment_id',
    ];

    protected $casts = [
        'is_red' => 'boolean',
    ];
}
