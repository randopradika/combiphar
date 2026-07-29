<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Employee Wellness Program" on the Karir tab (Figma 987:51) — a heading, a
 * lead paragraph, and a row of icon circles. The section did not exist in code
 * at all, so this creates its content table plus the two heading fields on the
 * contact Page record, and seeds the four programs from the design.
 *
 * Seeded icons point at the committed defaults in public/img/wellness/; an
 * admin can replace any of them with an upload, which stores a storage-disk
 * path instead (PageController::img() passes leading-slash paths through
 * untouched, so both forms resolve).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wellness_programs', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->nullable();
            $table->string('title_id')->nullable();
            $table->string('title_en')->nullable();
            $table->text('body_id')->nullable();
            $table->text('body_en')->nullable();
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->string('wellness_title_id')->nullable()->after('intro_en');
            $table->string('wellness_title_en')->nullable()->after('wellness_title_id');
            $table->text('wellness_desc_id')->nullable()->after('wellness_title_en');
            $table->text('wellness_desc_en')->nullable()->after('wellness_desc_id');
        });

        DB::table('pages')->where('slug', 'contact')->update([
            'wellness_title_id' => 'Employee Wellness Program',
            'wellness_title_en' => 'Employee Wellness Program',
            'wellness_desc_id' => 'Employee wellness program ini dirancang untuk meningkatkan kesadaran karyawan akan pentingnya kesehatan untuk hari esok yang lebih sehat.',
            'wellness_desc_en' => 'This employee wellness program is designed to raise awareness of how important health is to a healthier tomorrow.',
        ]);

        $now = now();

        DB::table('wellness_programs')->insert([
            [
                'icon' => '/img/wellness/football.png',
                'title_id' => 'Olahraga',
                'title_en' => 'Sports',
                'body_id' => 'Program kebugaran dengan olahraga dilakukan untuk memberikan kesempatan kepada karyawan untuk tetap aktif dan sehat.',
                'body_en' => 'Sports and fitness programmes give employees the chance to stay active and healthy.',
                'sort' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'icon' => '/img/wellness/gathering.png',
                'title_id' => 'Gathering',
                'title_en' => 'Gathering',
                'body_id' => 'Employee Wellness Program di Combiphar tidak hanya terbatas pada masalah kesehatan, tetapi juga mencakup kegiatan gathering karyawan yang dapat membangun hubungan sosial dan mempromosikan budaya kerja yang positif.',
                'body_en' => 'Combiphar’s Employee Wellness Program goes beyond health alone: employee gatherings build social ties and promote a positive working culture.',
                'sort' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'icon' => '/img/wellness/trophy.png',
                'title_id' => 'Employee Reward',
                'title_en' => 'Employee Reward',
                'body_id' => 'Program ini adalah program appresiasi yang diberikan kepada karyawan terbaik yang dilakukan secara tahunan.',
                'body_en' => 'An annual appreciation programme recognising our best-performing employees.',
                'sort' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'icon' => '/img/wellness/checkup.png',
                'title_id' => 'Medical Check-up',
                'title_en' => 'Medical Check-up',
                'body_id' => 'Sebagian besar karyawan mendapatkan benefit untuk Medical Check-up sehingga masing-masing karyawan dapat mengambil tindakan yang aktif untuk menjaga kesehatannya.',
                'body_en' => 'Most employees receive a medical check-up benefit, so everyone can take active steps to look after their health.',
                'sort' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('wellness_programs');

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'wellness_title_id',
                'wellness_title_en',
                'wellness_desc_id',
                'wellness_desc_en',
            ]);
        });
    }
};
