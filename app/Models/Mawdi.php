<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * جدول mawadi3 — موضع واحد من كتاب "التبيان المفصل لمتشابهات القرآن"،
 * مع نصه الجاهز (html_text: نص عادي + <span style="color:red">
 * للكلمات المتشابهة، جاهز للعرض مباشرة بالفرونت).
 */
class Mawdi extends Model
{
    protected $table = 'mawadi3';
    protected $primaryKey = 'mawdi_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'mawdi_id', 'mawdi_number', 'surah_id', 'reference_text',
        'start_page', 'end_page', 'plain_text', 'html_text',
    ];
}
