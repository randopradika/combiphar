<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $t) {
            $t->text('bio_id')->nullable()->after('role_en');
            $t->text('bio_en')->nullable()->after('bio_id');
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $t) {
            $t->dropColumn(['bio_id', 'bio_en']);
        });
    }
};