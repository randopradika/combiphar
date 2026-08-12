<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fill the empty per-page meta descriptions (plus home's missing meta
     * title) that the Pengaturan → Ringkasan SEO screen surfaced on
     * 2026-08-11: every page shipped with no meta description in either
     * language, so search engines invented their own snippets.
     *
     * CMS content is per-environment, so this ships as a data migration keyed
     * on the stable `slug` (CLAUDE.md §9). Every write is guarded on the
     * column being empty — copy an editor has since written in the CMS is
     * never overwritten — and down() only reverts values that still exactly
     * match what this migration wrote. All strings sit inside the thresholds
     * SeoOverview measures (title ≤ 60, description 70–160).
     */
    private const META = [
        'home' => [
            'meta_title_id' => 'Combiphar - Championing a Healthy Tomorrow',
            'meta_title_en' => 'Combiphar - Championing a Healthy Tomorrow',
            'meta_description_id' => 'Combiphar — Championing a Healthy Tomorrow. Perusahaan farmasi dan kesehatan konsumen terkemuka di Indonesia dengan produk terpercaya untuk keluarga.',
            'meta_description_en' => 'Combiphar — Championing a Healthy Tomorrow. A leading Indonesian pharmaceutical and consumer healthcare company with trusted products for families.',
        ],
        'about' => [
            'meta_description_id' => 'Kenali Combiphar — sejarah dan tonggak perjalanan sejak 1971, visi dan nilai, dewan komisaris dan direksi, fasilitas produksi, serta penghargaan kami.',
            'meta_description_en' => 'Get to know Combiphar — our history and milestones since 1971, vision and values, boards of commissioners and directors, facilities, and awards.',
        ],
        'products' => [
            'meta_description_id' => 'Jelajahi rangkaian produk Combiphar — obat dan produk kesehatan konsumen terpercaya untuk membantu keluarga Indonesia hidup lebih sehat setiap hari.',
            'meta_description_en' => "Explore Combiphar's range of trusted medicines and consumer healthcare products that help Indonesian families live healthier every day.",
        ],
        'csr' => [
            'meta_description_id' => 'Komitmen keberlanjutan Combiphar — program lingkungan, edukasi, kampanye kesehatan, olahraga, serta tata kelola untuk Indonesia yang lebih sehat.',
            'meta_description_en' => "Combiphar's sustainability commitment — environmental, education, health campaign, sports, and governance programs for a healthier Indonesia.",
        ],
        'news' => [
            'meta_description_id' => 'Berita dan artikel terbaru dari Combiphar — pembaruan korporasi, edukasi gaya hidup sehat, dan informasi produk untuk keluarga Indonesia.',
            'meta_description_en' => 'The latest news and articles from Combiphar — corporate updates, healthy lifestyle education, and product information for Indonesian families.',
        ],
        'investor' => [
            'meta_description_id' => 'Informasi investor Combiphar — laporan keuangan, laporan tahunan, keterbukaan informasi, dan dokumen tata kelola perusahaan.',
            'meta_description_en' => 'Combiphar investor relations — financial statements, annual reports, information disclosures, and corporate governance documents.',
        ],
        'contact' => [
            'meta_description_id' => 'Hubungi Combiphar — kirim pesan, temukan lokasi kantor kami di seluruh Indonesia, dan jelajahi peluang karir bersama kami.',
            'meta_description_en' => 'Contact Combiphar — send us a message, find our office locations across Indonesia, and explore career opportunities with us.',
        ],
    ];

    public function up(): void
    {
        foreach (self::META as $slug => $fields) {
            foreach ($fields as $column => $value) {
                DB::table('pages')
                    ->where('slug', $slug)
                    ->where(fn ($q) => $q->whereNull($column)->orWhere($column, ''))
                    ->update([$column => $value]);
            }
        }
    }

    public function down(): void
    {
        foreach (self::META as $slug => $fields) {
            foreach ($fields as $column => $value) {
                DB::table('pages')
                    ->where('slug', $slug)
                    ->where($column, $value)
                    ->update([$column => null]);
            }
        }
    }
};
