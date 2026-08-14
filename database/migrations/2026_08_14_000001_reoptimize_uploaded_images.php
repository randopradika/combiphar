<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Re-run per environment: uploads since the 2026_07_23 optimizer pass (home
 * hero, milestone slides, CSR galleries) went up unoptimized again — Lighthouse
 * measured ~5 MB of image savings on dev's homepage, e.g. a 1.5 MB milestone
 * JPEG serving a 320x300 slot. Storage is per-env (gitignored), so riding the
 * deploy's `migrate --force` is still the only automated path onto that box.
 * Idempotent: the min-kb threshold and the "no win" guard skip files the July
 * pass already shrank, and PNG->JPG conversions rewrite their own DB refs
 * (see OptimizeImages::replaceDbReferences).
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
