<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $t) {
            $t->string('hero_image')->nullable()->after('banner_image');
            $t->string('manifesto_image')->nullable()->after('hero_image');
            $t->string('cta_image')->nullable()->after('manifesto_image');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $t) {
            $t->dropColumn(['hero_image', 'manifesto_image', 'cta_image']);
        });
    }
};