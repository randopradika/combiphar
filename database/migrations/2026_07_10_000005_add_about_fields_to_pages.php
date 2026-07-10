<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $t) {
            foreach (['intro', 'vision', 'mission', 'values'] as $f) {
                $t->text($f . '_id')->nullable();
                $t->text($f . '_en')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $t) {
            $t->dropColumn(['intro_id', 'intro_en', 'vision_id', 'vision_en', 'mission_id', 'mission_en', 'values_id', 'values_en']);
        });
    }
};