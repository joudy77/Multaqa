<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * يصلح تشفير مزدوج (أو حتى ثلاثي، بيتعامل مع الحالتين تلقائياً) بجداول
 * التبيان المفصل (word_colors, mawadi3, segments).
 *
 * آمن التشغيل أكتر من مرة: قبل ما يلمس أي نص، يفحص إذا فيه أثر تشفير
 * غلط أصلاً (وجود حرف Ø أو Ù — ما بيظهر بنص عربي سليم إطلاقاً)؛ لو
 * النص نظيف بيتركه متل ما هو تماماً. فلو شغلتيه أكتر من مرة بالغلط،
 * أو لو جدول معين كان أصلاً مصحّح من تشغيلة سابقة، ما رح ينكسر شي.
 *
 * الاستخدام: php artisan mawadi:fix-encoding
 */
class FixMojibakeEncoding extends Command
{
    protected $signature = 'mawadi:fix-encoding';
    protected $description = 'يصلح تشفير النصوص العربية المزدوج/الثلاثي بجداول word_colors, mawadi3, segments';

    private const AFFECTED_COLUMNS = [
        'word_colors' => ['primary_key' => 'word_id', 'columns' => ['word_text']],
        'mawadi3' => [
            'primary_key' => 'mawdi_id',
            'columns' => ['reference_text', 'plain_text', 'html_text'],
        ],
        'segments' => [
            'primary_key' => 'segment_id',
            'columns' => ['reference_text', 'plain_text', 'html_text'],
        ],
    ];

    /** أقصى عدد محاولات تصحيح متتالية على نفس النص (يغطي تشفير ثلاثي). */
    private const MAX_PASSES = 3;

    /** [ "table:primary_key" => "سبب الفشل" ] لتقرير آخر الأمر. */
    private array $failures = [];

    /** عداد الصفوف يلي انلمست فعلياً (كانت مشوهة وصححناها) — للتقرير بس. */
    private int $touchedCount = 0;

    public function handle(): int
    {
        foreach (self::AFFECTED_COLUMNS as $table => $meta) {
            $this->fixTable($table, $meta['primary_key'], $meta['columns']);
        }

        $this->newLine();
        $this->info("عدد الأعمدة يلي فعلياً انصلحت: {$this->touchedCount}");

        if (empty($this->failures)) {
            $this->info('تم إصلاح الترميز بكل الجداول بلا أي مشاكل.');
        } else {
            $this->warn(count($this->failures) . ' صف تعذّر إصلاحه بالكامل (نص تالف بشكل غير قابل للاسترجاع) — قائمة تفصيلية:');
            foreach ($this->failures as $ref => $reason) {
                $this->line("  - {$ref}: {$reason}");
            }
        }

        return self::SUCCESS;
    }

    private function fixTable(string $table, string $primaryKey, array $columns): void
    {
        $rows = DB::table($table)->get(array_merge([$primaryKey], $columns));
        $this->info("جدول {$table}: {$rows->count()} صف...");

        $bar = $this->output->createProgressBar($rows->count());
        foreach ($rows as $row) {
            $updates = [];
            foreach ($columns as $column) {
                $original = $row->{$column};
                if ($original === null || $original === '' || !$this->looksMojibake($original)) {
                    continue; // نظيف أصلاً — ما نلمسه، وهيك التشغيل الثاني آمن.
                }
                $fixed = $this->fixMojibake($original);
                if ($fixed !== $original) {
                    $updates[$column] = $fixed;
                }
            }

            if (!empty($updates)) {
                try {
                    DB::table($table)->where($primaryKey, $row->{$primaryKey})->update($updates);
                    $this->touchedCount += count($updates);
                } catch (Throwable $e) {
                    $this->failures["{$table}:{$row->{$primaryKey}}"] = $e->getMessage();
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
    }

    /**
     * يطبّق التصحيح لغاية 3 مرات متتالية لغاية ما يصير النص نظيف
     * (يغطي تشفير ثلاثي مو بس مزدوج)، ويتوقف فوراً لو محاولة تالية
     * رح تنتج UTF-8 غير صالح (بيرجع آخر نسخة صالحة وصلها).
     */
    private function fixMojibake(string $text): string
    {
        $current = $text;
        for ($pass = 0; $pass < self::MAX_PASSES; $pass++) {
            if (!$this->looksMojibake($current)) {
                break;
            }
            $candidate = mb_convert_encoding($current, 'ISO-8859-1', 'UTF-8');
            if ($candidate === '' || !mb_check_encoding($candidate, 'UTF-8')) {
                break;
            }
            $current = $candidate;
        }
        return $current;
    }

    /** وجود Ø أو Ù بنص عربي = دليل شبه مؤكد على تشفير مضاعف (حرفين لاتينيين ما بيظهروا بعربي سليم إطلاقاً). */
    private function looksMojibake(string $text): bool
    {
        return str_contains($text, 'Ø') || str_contains($text, 'Ù');
    }
}