<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $t) {
            $t->string('banner_title2_id')->nullable();
            $t->string('banner_title2_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $t) {
            $t->dropColumn(['banner_title2_id', 'banner_title2_en']);
        });
    }
};
