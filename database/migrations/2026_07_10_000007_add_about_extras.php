<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('region')->nullable();
            $t->string('plants')->nullable();
            $t->string('area')->nullable();
            $t->text('detail_id')->nullable();
            $t->text('detail_en')->nullable();
            $t->string('image')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('accreditations', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('issuer')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('offices', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('city')->nullable();
            $t->string('category')->nullable();
            $t->text('description_id')->nullable();
            $t->text('description_en')->nullable();
            $t->string('phone')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::table('pages', function (Blueprint $t) {
            $t->string('stat1_value')->nullable();
            $t->string('stat1_label_id')->nullable();
            $t->string('stat1_label_en')->nullable();
            $t->string('stat2_value')->nullable();
            $t->string('stat2_label_id')->nullable();
            $t->string('stat2_label_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
        Schema::dropIfExists('accreditations');
        Schema::dropIfExists('offices');
        Schema::table('pages', function (Blueprint $t) {
            $t->dropColumn(['stat1_value', 'stat1_label_id', 'stat1_label_en', 'stat2_value', 'stat2_label_id', 'stat2_label_en']);
        });
    }
};