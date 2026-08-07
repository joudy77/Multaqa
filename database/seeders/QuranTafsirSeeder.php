<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuranTafsirSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tafsirs')->truncate();

        DB::connection('src_tafsirs')->table('tafsirs') // عدّلي الاسم لو مختلف
            ->orderBy('id')
            ->chunk(500, function ($rows) {
                $data = [];

                foreach ($rows as $row) {
                    // استخراج رقم السورة من ayah_key، مثال "5:84" => 5
                    [$surah, ] = explode(':', $row->ayah_key);

                    $data[] = [
                        'ayah_key' => $row->ayah_key,
                        'ayah_keys' => $row->ayah_keys ?? null,
                        'surah' => (int) $surah,
                        'from_ayah' => (int) $row->from_ayah,
                        'to_ayah' => (int) $row->to_ayah,
                        'group_ayah_key' => $row->group_ayah_key ?? null,
                        'text' => strip_tags($row->text), // إزالة أي وسوم HTML من النص
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                DB::table('tafsirs')->insert($data);
            });

        $this->command->info('✅ تم استيراد تفسير المختصر بنجاح');
    }
}