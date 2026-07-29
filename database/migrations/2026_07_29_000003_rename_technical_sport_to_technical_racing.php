<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rename the CSR sports card "Technical Sport" → "Technical Racing" in both
 * locales. Content lives per environment, so the rename ships as a migration
 * rather than a CMS edit that would have to be repeated on every box.
 *
 * `slug` is deliberately untouched: /csr-komunitas/technical-sport stays a
 * working URL, and CsrProgram only derives a slug when it is blank.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('csr_programs')
            ->where('category', 'sports')
            ->where(function ($q) {
                $q->where('title_id', 'Technical Sport')
                    ->orWhere('title_en', 'Technical Sport');
            })
            ->update([
                'title_id' => 'Technical Racing',
                'title_en' => 'Technical Racing',
            ]);
    }

    public function down(): void
    {
        DB::table('csr_programs')
            ->where('category', 'sports')
            ->where(function ($q) {
                $q->where('title_id', 'Technical Racing')
                    ->orWhere('title_en', 'Technical Racing');
            })
            ->update([
                'title_id' => 'Technical Sport',
                'title_en' => 'Technical Sport',
            ]);
    }
};
