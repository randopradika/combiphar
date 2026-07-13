<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_banners', function (Blueprint $t) {
            $t->id();
            $t->string('title_id')->nullable();
            $t->string('title_en')->nullable();
            $t->string('image')->nullable();
            $t->string('link')->nullable();
            $t->integer('sort')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_banners');
    }
};
