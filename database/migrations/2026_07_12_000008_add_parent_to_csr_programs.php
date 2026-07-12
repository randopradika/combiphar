<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('csr_programs', function (Blueprint $t) {
            $t->foreignId('parent_id')->nullable()->after('id')
                ->constrained('csr_programs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('csr_programs', function (Blueprint $t) {
            $t->dropConstrainedForeignId('parent_id');
        });
    }
};
