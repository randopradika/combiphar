<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('csr_programs', function (Blueprint $table) {
            // Photo grid for the Sports detail page (array of image paths).
            $table->json('gallery')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('csr_programs', function (Blueprint $table) {
            $table->dropColumn('gallery');
        });
    }
};
