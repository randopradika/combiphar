<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hides the Berita "Investor Update" surface site-wide until an editor
     * turns it on: the tab on the News page AND the nav dropdown / mobile
     * drill-down item (HandleInertiaRequests filters nav.menus.news on the
     * same flag). Toggled from the Artikel list's header action — deliberately
     * default FALSE, per the request to hide it now.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('show_investor_tab')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('show_investor_tab');
        });
    }
};
