<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Investor hub sub-menu cards (Figma 577:1260). The `key` maps to the section
 * the card opens on the Investor page, so the row set is fixed and seeded
 * here — admins edit title/image/order/visibility, never create or delete.
 */
return new class extends Migration
{
    /** key => [title_id, title_en] — mirrors the hardcoded list in Investor.jsx. */
    private const CARDS = [
        'stock' => ['Informasi Saham', 'Stock Information'],
        'financial' => ['Informasi Keuangan', 'Financial Information'],
        'annual' => ['Laporan Tahunan', 'Annual Report'],
        'sustainability' => ['Laporan Keberlanjutan', 'Sustainability Report'],
        'presentation' => ['Presentasi Perusahaan', 'Company Presentation'],
        'disclosures' => ['Keterbukaan Informasi', 'Information Disclosures'],
        'shareholder' => ['Pemegang Saham', 'Shareholder'],
        'contact' => ['Kontak IR', 'IR Contact'],
    ];

    public function up(): void
    {
        Schema::create('investor_hub_cards', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('title_id')->nullable();
            $table->string('title_en')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        $now = now();
        $sort = 0;
        $rows = [];

        foreach (self::CARDS as $key => [$id, $en]) {
            $rows[] = [
                'key' => $key,
                'title_id' => $id,
                'title_en' => $en,
                'is_visible' => true,
                'sort' => ++$sort,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('investor_hub_cards')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_hub_cards');
    }
};
