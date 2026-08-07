<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportVersesForEmbedding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-verses-for-embedding';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    // public function handle()
    // {
    //     //
    // }
    public function handle()
{
    $verses = DB::table('quran_verses')->get();
    $tafsirs = DB::table('tafsirs')->get();

    $enriched = $verses->map(function ($verse) use ($tafsirs) {
        $tafsir = $tafsirs->first(fn ($t) =>
            $t->surah == $verse->surah &&
            $verse->ayah >= $t->from_ayah &&
            $verse->ayah <= $t->to_ayah
        );

        return [
            'verse_id' => $verse->id,
            'verse_key' => $verse->verse_key,
            'text' => $verse->text,
            'tafsir_snippet' => $tafsir->text ?? '',
            'combined_text' => $verse->text . ' ' . ($tafsir->text ?? ''),
        ];
    });

    file_put_contents(
        storage_path('app/verses_enriched.json'),
        json_encode($enriched, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );

    $this->info('✅ تم تصدير ' . $enriched->count() . ' آية جاهزة للـ embeddings');
}
}
