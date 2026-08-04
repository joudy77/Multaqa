<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    protected $table = 'segments';
    protected $primaryKey = 'segment_id';
    public $timestamps = false;

    protected $fillable = [
        'mawdi_id', 'page_number', 'reference_text', 'reference_surah',
        'ayah_start', 'ayah_end', 'plain_text', 'html_text', 'red_parts_json'
    ];
}
