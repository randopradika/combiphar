<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Per-locale article slugs, mirroring combiphar.com: /id/berita/{slug} keeps the
 * Indonesian slug in `slug`, /en/news/{slug_en} gets the English one. `slug_en`
 * is nullable — an article without one simply keeps using `slug` on /en, exactly
 * as before — and unique, so an English slug can never point at two articles.
 *
 * Then `news:import-combiphar-en` runs once more: it now also writes `slug_en`
 * from the source's English slug for the 177 imported articles. Guarded like
 * 2026_08_18_000001 — a network failure logs and echoes instead of failing the
 * deploy; re-run by hand with `php artisan news:import-combiphar-en` if the
 * summary line is missing from the deploy log.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('slug_en')->nullable()->unique()->after('slug');
        });

        try {
            Artisan::call('news:import-combiphar-en');
            $summary = trim((string) last(array_filter(explode("\n", Artisan::output()))));
        } catch (Throwable $e) {
            $summary = 'FAILED — '.$e->getMessage().' (re-run: php artisan news:import-combiphar-en)';
        }

        Log::info('[2026_08_18_000002] news:import-combiphar-en: '.$summary);
        echo "news:import-combiphar-en: {$summary}\n";
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropUnique(['slug_en']);
            $table->dropColumn('slug_en');
        });
    }
};
