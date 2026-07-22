<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Board" detail layout for CSR/Governance pages (Figma 967:78 — Komite Audit):
 * intro content, then the Audit Committee + Corporate Secretary member grids
 * (same as the About page), then a second rich content block for the duties /
 * charter text below the grids. Flags the existing "komite-audit" program to use it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('csr_programs', function (Blueprint $table) {
            if (! Schema::hasColumn('csr_programs', 'content2_id')) {
                $table->text('content2_id')->nullable()->after('content_en');
            }
            if (! Schema::hasColumn('csr_programs', 'content2_en')) {
                $table->text('content2_en')->nullable()->after('content2_id');
            }
        });

        DB::table('csr_programs')->where('slug', 'komite-audit')->update(['layout' => 'board']);
    }

    public function down(): void
    {
        DB::table('csr_programs')->where('slug', 'komite-audit')->where('layout', 'board')->update(['layout' => null]);

        Schema::table('csr_programs', function (Blueprint $table) {
            $table->dropColumn(['content2_id', 'content2_en']);
        });
    }
};
