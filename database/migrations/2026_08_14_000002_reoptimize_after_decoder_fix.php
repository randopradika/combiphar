<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Second pass of the 2026_08_14 re-optimization: the 000001 run skipped every
 * PNG saved under a .jpg name as "unreadable" (the decoder was chosen by
 * extension), which is exactly what dev's biggest offenders are — the 1.5 MB
 * milestone slide and ~1 MB seed photos. OptimizeImages now decodes by
 * content, so re-run it. Idempotent for everything the earlier passes
 * already shrank.
 */
return new class extends Migration
{
    public function up(): void
    {
        Artisan::call('images:optimize');
    }

    public function down(): void
    {
        // Lossy in-place rewrite — nothing to restore.
    }
};
