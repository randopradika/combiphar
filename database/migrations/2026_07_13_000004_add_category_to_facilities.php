<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            // bold product-category line above the dosage detail (Figma 577:3342)
            $table->string('category_id')->nullable()->after('area');
            $table->string('category_en')->nullable()->after('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn(['category_id', 'category_en']);
        });
    }
};
