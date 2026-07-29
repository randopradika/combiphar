<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The recruitment-fraud warning band on Karir & Kontak (Figma 987:51). The
 * section existed but was one hardcoded sentence with a triangle icon; the
 * design is a headline plus numbered notes, so both become CMS content.
 *
 * `fraud_title_*` keeps its line break — the design sets the headline on two
 * lines and the page renders it with white-space: pre-line, so an admin
 * controls where it wraps. `fraud_items` is a JSON list of bilingual rich-text
 * notes, numbered 1..N on the page, so notes can be added or removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->text('fraud_title_id')->nullable()->after('wellness_desc_en');
            $table->text('fraud_title_en')->nullable()->after('fraud_title_id');
            $table->json('fraud_items')->nullable()->after('fraud_title_en');
        });

        DB::table('pages')->where('slug', 'contact')->update([
            'fraud_title_id' => "HATI-HATI PENIPUAN\nLOWONGAN KERJA",
            'fraud_title_en' => "BEWARE OF JOB\nVACANCY FRAUD",
            'fraud_items' => json_encode([
                [
                    'text_id' => '<p>Combiphar <strong>TIDAK PERNAH memungut biaya apapun</strong> dalam proses rekrutmen pekerjaan.</p>',
                    'text_en' => '<p>Combiphar <strong>NEVER charges any fee</strong> during the recruitment process.</p>',
                ],
                [
                    'text_id' => '<p>Laporkan dugaan penipuan rekrutmen Combiphar ke <strong>Call Center 0800-1-800088</strong> (Bebas Pulsa) atau 0818 0680 0088 (SMS &amp; WhatsApp) atau Instagram <strong>@CombiCareCenter</strong></p>',
                    'text_en' => '<p>Report suspected Combiphar recruitment fraud to <strong>Call Center 0800-1-800088</strong> (toll-free), 0818 0680 0088 (SMS &amp; WhatsApp), or Instagram <strong>@CombiCareCenter</strong></p>',
                ],
            ]),
        ]);
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['fraud_title_id', 'fraud_title_en', 'fraud_items']);
        });
    }
};
