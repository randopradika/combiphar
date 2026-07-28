<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CMS-editable heading + description for the CSR page's Health Campaign
     * block. Both were hardcoded in Csr.jsx and removed outright in 904b803;
     * as `pages` columns an admin can edit them — or blank them to hide the
     * heading again — without a code change. Seeded with the original copy so
     * the block comes back exactly as it was.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('health_title_id')->nullable()->after('intro_en');
            $table->string('health_title_en')->nullable()->after('health_title_id');
            $table->text('health_desc_id')->nullable()->after('health_title_en');
            $table->text('health_desc_en')->nullable()->after('health_desc_id');
        });

        DB::table('pages')->where('slug', 'csr')->update([
            'health_title_id' => 'Health Campaign',
            'health_title_en' => 'Health Campaign',
            'health_desc_id' => 'Melalui berbagai Health Campaign, Combiphar menghadirkan inisiatif yang mendukung kesehatan, pemberdayaan, pendidikan, dan gaya hidup aktif untuk menciptakan dampak positif bagi masyarakat Indonesia.',
            'health_desc_en' => 'Through various Health Campaigns, Combiphar delivers initiatives supporting health, empowerment, education, and an active lifestyle to create a positive impact for Indonesian society.',
        ]);
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'health_title_id',
                'health_title_en',
                'health_desc_id',
                'health_desc_en',
            ]);
        });
    }
};
