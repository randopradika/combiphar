<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The footer copyright line was CMS-editable in name only: the columns have
     * existed since 2026_07_15_000001 but were never populated, so every
     * environment rendered SiteLayout's hardcoded "All Rights Reserved to
     * Combiphar" fallback instead. Editing the CMS field therefore looked like
     * it did nothing until you typed into it, and there was no way to see the
     * current copy in the admin.
     *
     * Seeds the live copy so the field shows what the site actually renders,
     * and widens the columns to text because the field becomes a RichEditor —
     * "Combiphar" is bold in the design (.footer__rights strong), which a plain
     * string input cannot express.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->text('footer_copyright_id')->nullable()->change();
            $table->text('footer_copyright_en')->nullable()->change();
        });

        // Only fill what is empty — never overwrite copy an admin already set.
        DB::table('pages')
            ->where('slug', 'home')
            ->where(fn ($q) => $q->whereNull('footer_copyright_id')->orWhere('footer_copyright_id', ''))
            ->update(['footer_copyright_id' => '<p>Hak Cipta Dilindungi <strong>Combiphar</strong></p>']);

        DB::table('pages')
            ->where('slug', 'home')
            ->where(fn ($q) => $q->whereNull('footer_copyright_en')->orWhere('footer_copyright_en', ''))
            ->update(['footer_copyright_en' => '<p>All Rights Reserved to <strong>Combiphar</strong></p>']);
    }

    /**
     * Deliberately does not blank the copy: with the JSX fallback gone, doing so
     * would leave the footer with no copyright line at all.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('footer_copyright_id')->nullable()->change();
            $table->string('footer_copyright_en')->nullable()->change();
        });
    }
};
