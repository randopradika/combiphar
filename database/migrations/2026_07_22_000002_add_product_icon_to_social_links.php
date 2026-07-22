<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A social link now carries two icons: `icon` (footer — white/transparent on
     * the dark footer) and `product_icon` (colored logo shown in the product
     * detail popup's "Informasi Lebih Lanjut" section, on a light background).
     * The popup matches a product's Instagram/Facebook link to the SocialLink of
     * the same platform (by name) and uses that platform's product_icon.
     */
    public function up(): void
    {
        Schema::table('social_links', function (Blueprint $table) {
            $table->string('product_icon')->nullable()->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('social_links', function (Blueprint $table) {
            $table->dropColumn('product_icon');
        });
    }
};
