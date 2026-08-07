<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranWord extends Model
{
    protected $table = 'quran_words';
    public $timestamps = false;
    protected $fillable = ['word_key', 'surah_number', 'ayah_number', 'word_position', 'word_text'];
}