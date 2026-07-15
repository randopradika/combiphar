<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $t) {
            // Uploaded MP4 for the home manifesto. Takes priority over the
            // external `manifesto_video` URL (YouTube / Vimeo / MP4 link).
            $t->string('manifesto_video_file')->nullable()->after('manifesto_video');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $t) {
            $t->dropColumn('manifesto_video_file');
        });
    }
};
