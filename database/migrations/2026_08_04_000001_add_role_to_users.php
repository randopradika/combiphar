<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Default 'editor', not 'admin': a row created without an explicit
            // role must get the LEAST privilege, not the most. Deliberately not
            // in the model's Fillable list either, so no mass-assignment path
            // can promote an account.
            $table->string('role', 20)->default('editor')->after('email');
        });

        // Before this column existed, canAccessPanel() returned true for every
        // row — so every existing account already had full CMS access. Promote
        // them all to keep that working; demote deliberately from here on.
        // Doing it the other way round would lock the client out of their CMS
        // on deploy.
        DB::table('users')->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
