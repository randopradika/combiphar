<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Whether a board member appears on the public page. The admin flips it from
 * the list table, so a commissioner or director can be taken off the About page
 * (and off the Komite Audit board layout) without deleting the record and the
 * bio that goes with it.
 *
 * Default true: every existing member keeps showing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->boolean('show_on_page')->default(true)->after('group');
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn('show_on_page');
        });
    }
};
