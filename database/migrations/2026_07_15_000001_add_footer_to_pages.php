<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            // Footer settings (site-wide) — edited on the "home" page record.
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->string('footer_copyright_id')->nullable();
            $table->string('footer_copyright_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_url', 'instagram_url', 'youtube_url', 'linkedin_url', 'tiktok_url',
                'footer_copyright_id', 'footer_copyright_en',
            ]);
        });
    }
};
