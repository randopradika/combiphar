<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_vacancies', function (Blueprint $t) {
            $t->text('summary_id')->nullable()->after('location');
            $t->text('summary_en')->nullable()->after('summary_id');
        });
    }

    public function down(): void
    {
        Schema::table('job_vacancies', function (Blueprint $t) {
            $t->dropColumn(['summary_id', 'summary_en']);
        });
    }
};
