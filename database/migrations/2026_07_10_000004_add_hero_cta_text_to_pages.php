<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $t) {
            $t->string('hero_line1_id')->nullable()->after('hero_image');
            $t->string('hero_line1_en')->nullable()->after('hero_line1_id');
            $t->string('hero_line2_id')->nullable()->after('hero_line1_en');
            $t->string('hero_line2_en')->nullable()->after('hero_line2_id');
            $t->string('cta_title_id')->nullable()->after('cta_image');
            $t->string('cta_title_en')->nullable()->after('cta_title_id');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $t) {
            $t->dropColumn(['hero_line1_id', 'hero_line1_en', 'hero_line2_id', 'hero_line2_en', 'cta_title_id', 'cta_title_en']);
        });
    }
};