<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QuranImportSeeder extends Seeder
{
    public function run(): void
    {
        // 1. نقل الكلمات (مهم نحافظ على نفس الـ id الأصلي)
        Schema::disableForeignKeyConstraints();
        DB::table('quran_words')->truncate();

        DB::connection('src_words')->table('words')
            ->orderBy('id')
            ->chunk(1000, function ($rows) {
                $data = [];
                foreach ($rows as $row) {
                    $data[] = [
                        'id' => $row->id,
                        'location' => $row->location,
                        'surah' => $row->surah,
                        'ayah' => $row->ayah,
                        'word' => $row->word,
                        'text' => $row->text,
                    ];
                }
                DB::table('quran_words')->insert($data);
            });

        $this->command->info('✅ تم نقل الكلمات');

        // 2. نقل الصفحات
        DB::table('quran_pages')->truncate();

        DB::connection('src_pages')->table('pages')
            ->orderBy('page_number')->orderBy('line_number')
            ->chunk(1000, function ($rows) {
                $data = [];
                // foreach ($rows as $row) {
                //     $data[] = [
                //         'page_number' => $row->page_number,
                //         'line_number' => $row->line_number,
                //         'line_type' => $row->line_type,
                //         'is_centered' => $row->is_centered,
                //         'first_word_id' => $row->first_word_id,
                //         'last_word_id' => $row->last_word_id,
                //         'surah_number' => $row->surah_number,
                //     ];
                // }
                foreach ($rows as $row) {
    $data[] = [
        'page_number' => $row->page_number,
        'line_number' => $row->line_number,
        'line_type' => $row->line_type,
        'is_centered' => $row->is_centered,
        'first_word_id' => $row->first_word_id === '' ? null : $row->first_word_id,
        'last_word_id' => $row->last_word_id === '' ? null : $row->last_word_id,
        'surah_number' => $row->surah_number === '' ? null : $row->surah_number,
    ];
}
                DB::table('quran_pages')->insert($data);
            });

        $this->command->info('✅ تم نقل الصفحات');

        // 3. نقل الآيات
        DB::table('quran_verses')->truncate();

        DB::connection('src_verses')->table('verses')
            ->orderBy('id')
            ->chunk(1000, function ($rows) {
                $data = [];
                foreach ($rows as $row) {
                    $data[] = [
                        'verse_key' => $row->verse_key,
                        'surah' => $row->surah,
                        'ayah' => $row->ayah,
                        'text' => $row->text,
                    ];
                }
                DB::table('quran_verses')->insert($data);
            });

        $this->command->info('✅ تم نقل الآيات');
        Schema::enableForeignKeyConstraints();
    }
}