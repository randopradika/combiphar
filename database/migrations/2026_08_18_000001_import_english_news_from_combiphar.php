<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * One-time per-environment run of `news:import-combiphar-en`: the July import
 * (`news:import-combiphar`) read combiphar.com with locale=id and copied the
 * Indonesian text into BOTH language columns, so every /en article rendered
 * Indonesian. The command fills title_en / excerpt_en / body_en, mapped by the
 * remote article id → Indonesian slug, and never touches the *_id columns.
 *
 * Shipped as a migration because the dev deploy runs `migrate` but never runs
 * importers, and no shell on the box is available from here. Idempotent — a
 * second run reports `updated=0`.
 *
 * Guarded: a network failure to combiphar.com must not fail the deploy. If the
 * summary line below is missing from the deploy log (or /en/news still lists
 * Indonesian titles), re-run by hand: `php artisan news:import-combiphar-en`.
 */
return new class extends Migration
{
    public function up(): void
    {
        try {
            Artisan::call('news:import-combiphar-en');
            $summary = trim((string) last(array_filter(explode("\n", Artisan::output()))));
        } catch (Throwable $e) {
            $summary = 'FAILED — '.$e->getMessage().' (re-run: php artisan news:import-combiphar-en)';
        }

        Log::info('[2026_08_18_000001] news:import-combiphar-en: '.$summary);
        echo "news:import-combiphar-en: {$summary}\n";
    }

    public function down(): void
    {
        // The pre-import state was Indonesian text duplicated into *_en; restoring
        // that would also clobber any English an editor has since written. Nothing to undo.
    }
};
