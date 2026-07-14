<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            // Clickable text under the Skala Kami description that opens the facilities pop-up.
            $table->string('presence_popup_text_id')->nullable();
            $table->string('presence_popup_text_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['presence_popup_text_id', 'presence_popup_text_en']);
        });
    }
};
