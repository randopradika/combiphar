<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_vacancies', function (Blueprint $t) {
            $t->longText('requirements_id')->nullable()->after('description_en');
            $t->longText('requirements_en')->nullable()->after('requirements_id');
            $t->string('apply_url')->nullable()->after('requirements_en');
        });
    }

    public function down(): void
    {
        Schema::table('job_vacancies', function (Blueprint $t) {
            $t->dropColumn(['requirements_id', 'requirements_en', 'apply_url']);
        });
    }
};
