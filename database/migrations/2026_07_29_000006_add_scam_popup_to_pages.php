<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Recruitment Scam" pop-up shown when the Karir tab opens (Figma 987:258).
 *
 * Everything is CMS content on the contact Page record, including the on/off
 * toggle — the warning is time-sensitive, so an admin must be able to retire it
 * without a deploy. `scam_items` is a JSON list of icon + bilingual rich-text
 * blocks so notes can be added or removed.
 *
 * Convention inside the rich text: bold renders white, italic renders magenta
 * and upright (the design colours the domains). The field helper text says so.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('scam_popup_enabled')->default(false)->after('fraud_items');
            $table->string('scam_title_id')->nullable()->after('scam_popup_enabled');
            $table->string('scam_title_en')->nullable()->after('scam_title_id');
            $table->json('scam_items')->nullable()->after('scam_title_en');
            $table->text('scam_note_id')->nullable()->after('scam_items');
            $table->text('scam_note_en')->nullable()->after('scam_note_id');
        });

        DB::table('pages')->where('slug', 'contact')->update([
            'scam_popup_enabled' => true,
            'scam_title_id' => 'Recruitment Scam',
            'scam_title_en' => 'Recruitment Scam',
            'scam_items' => json_encode([
                [
                    'icon' => '/img/scam/cashless.png',
                    'text_id' => '<p>Combiphar <strong>TIDAK PERNAH</strong> memungut biaya apapun dalam proses rekrutmen pekerjaan.</p>',
                    'text_en' => '<p>Combiphar <strong>NEVER</strong> charges any fee during the recruitment process.</p>',
                ],
                [
                    'icon' => '/img/scam/domain.png',
                    'text_id' => '<p>Seluruh komunikasi hanya melalui domain <em>combiphar.com airmancur.co.id</em> dan <em>simba.com</em></p>',
                    'text_en' => '<p>All communication goes only through the domains <em>combiphar.com airmancur.co.id</em> and <em>simba.com</em></p>',
                ],
            ]),
            'scam_note_id' => 'Jika memiliki informasi terkait rekrutmen Combiphar yang diduga penipuan atau yang perlu dikonfirmasi lebih lanjut, dapat menghubungi Call Center 0800-1-800088 (Bebas Pulsa) atau 0818 06 800088 (SMS & WhatsApp) atau @CombiCareCenter pada hari Senin-Jumat pukul 08.00-17.00',
            'scam_note_en' => 'If you have information about Combiphar recruitment that you suspect is fraudulent, or that needs confirming, contact our Call Center on 0800-1-800088 (toll-free), 0818 06 800088 (SMS & WhatsApp) or @CombiCareCenter, Monday–Friday 08.00–17.00.',
        ]);
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'scam_popup_enabled',
                'scam_title_id',
                'scam_title_en',
                'scam_items',
                'scam_note_id',
                'scam_note_en',
            ]);
        });
    }
};
