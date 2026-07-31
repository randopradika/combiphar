<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Whether a page appears in the site menu — header nav, mobile menu and footer
 * all render the same list.
 *
 * Investor is hidden for now. That ships as a migration rather than a CMS edit
 * repeated on every box, the way the Technical Racing rename did. The admin
 * toggle turns it back on without a deploy, and the /investor route keeps
 * resolving either way, so existing links and the sitemap are unaffected.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('show_in_menu')->default(true)->after('under_development');
        });

        DB::table('pages')->where('slug', 'investor')->update(['show_in_menu' => false]);
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('show_in_menu');
        });
    }
};
