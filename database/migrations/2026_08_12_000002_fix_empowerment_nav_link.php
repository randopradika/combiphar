<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The top-nav "Empowerment" item pointed at /combi-hope-youth-empowerment
     * while the live programme's slug is `empowerment` — a 404 straight from
     * the nav (reported on dev, 2026-08-12). Nav rows are per-environment
     * (`nav_items`, seeded from the lang files), so this ships as a data
     * migration; the lang-file fallback is fixed in the same commit.
     */
    public function up(): void
    {
        DB::table('nav_items')
            ->where('suffix', '/combi-hope-youth-empowerment')
            ->update(['suffix' => '/empowerment']);

        // Environments still carrying the long programme slug (local does; dev
        // does not) converge on the canonical one — but never by clobbering an
        // existing `empowerment` row.
        if (! DB::table('csr_programs')->where('slug', 'empowerment')->exists()) {
            DB::table('csr_programs')
                ->where('slug', 'combi-hope-youth-empowerment')
                ->update(['slug' => 'empowerment']);
        }
    }

    public function down(): void
    {
        DB::table('nav_items')
            ->where('suffix', '/empowerment')
            ->update(['suffix' => '/combi-hope-youth-empowerment']);

        // Only a row this migration could have renamed is renamed back; a
        // programme that was always `empowerment` (dev) is left alone.
        if (! DB::table('csr_programs')->where('slug', 'combi-hope-youth-empowerment')->exists()) {
            DB::table('csr_programs')
                ->where('slug', 'empowerment')
                ->where('title_en', 'like', '%Combi Hope%')
                ->update(['slug' => 'combi-hope-youth-empowerment']);
        }
    }
};
