<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuranDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $files = [
            'export_quran_words.sql',
            'export_surahs.sql',
            'export_mawadi3.sql',
            'export_segments.sql',
            'export_word_colors.sql',
        ];

        foreach ($files as $file) {
            $path = database_path("exports/{$file}");

            if (!file_exists($path)) {
                $this->command->warn("الملف مش موجود: {$file}");
                continue;
            }

            $sql = file_get_contents($path);
            DB::unprepared($sql); // بيقرا الملف كـ UTF-8 مباشرة عبر PHP، بلا مرور بطرفية ويندوز

            $this->command->info("تم استيراد: {$file}");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}