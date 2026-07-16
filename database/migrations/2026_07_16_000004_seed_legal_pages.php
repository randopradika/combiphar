<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Guarantee the two legal-page rows exist so the pages resolve and the CMS
     * resource ("Halaman Legal (Footer)") always has records to edit. Content is
     * filled per-environment via `php artisan legal:import-combiphar` or the CMS.
     * insertOrIgnore is idempotent against the unique `key` column.
     */
    public function up(): void
    {
        $now = now();
        DB::table('legal_pages')->insertOrIgnore([
            ['key' => 'terms', 'title_id' => 'Syarat & Ketentuan', 'title_en' => 'Terms of Use', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'privacy', 'title_id' => 'Kebijakan Privasi', 'title_en' => 'Privacy Policy', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        DB::table('legal_pages')->whereIn('key', ['terms', 'privacy'])->delete();
    }
};
