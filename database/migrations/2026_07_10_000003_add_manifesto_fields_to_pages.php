<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $t) {
            $t->string('manifesto_title_id')->nullable()->after('manifesto_image');
            $t->string('manifesto_title_en')->nullable()->after('manifesto_title_id');
            $t->string('manifesto_video')->nullable()->after('manifesto_title_en');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $t) {
            $t->dropColumn(['manifesto_title_id', 'manifesto_title_en', 'manifesto_video']);
        });
    }
};