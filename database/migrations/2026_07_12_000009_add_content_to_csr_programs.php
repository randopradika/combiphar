<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('csr_programs', function (Blueprint $t) {
            $t->longText('content_id')->nullable()->after('body_en');
            $t->longText('content_en')->nullable()->after('content_id');
        });
    }

    public function down(): void
    {
        Schema::table('csr_programs', function (Blueprint $t) {
            $t->dropColumn(['content_id', 'content_en']);
        });
    }
};
