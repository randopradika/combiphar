<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Award;
use App\Models\CsrProgram;
use App\Models\GlobalSite;
use App\Models\ImpactProgram;
use App\Models\InvestorDocument;
use App\Models\JobVacancy;
use App\Models\Milestone;
use App\Models\OnlineShop;
use App\Models\Page;
use App\Models\Person;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'pages',
            'articles',
            'products',
            'product_categories',
            'people',
            'awards',
            'milestones',
            'impact_programs',
            'csr_programs',
            'investor_documents',
            'job_vacancies',
            'global_sites',
            'online_shops',
        ] as $table) {
            DB::table($table)->delete();
        }

        Schema::enableForeignKeyConstraints();

        // ---- Pages (banners / meta) ----
        $pages = [
            ['about', 'Tentang Kami', 'About Us'],
            ['products', 'Produk', 'Products'],
            ['csr', 'Tanggung Jawab Sosial Perusahaan', 'Corporate Social Responsibility'],
            ['investor', 'Investor Relations', 'Investor Relations'],
            ['news', 'Berita dan Info Kesehatan', 'News and Health Information'],
            ['contact', 'Karir dan Kontak', 'Careers and Contact'],
        ];

        foreach ($pages as [$slug, $idT, $enT]) {
            Page::create([
                'slug' => $slug,
                'banner_title_id' => $idT,
                'banner_title_en' => $enT,
                'meta_title_id' => $idT . ' - Combiphar',
                'meta_title_en' => $enT . ' - Combiphar',
            ]);
        }

        // ---- Product categories + products ----
        $cats = [
            ['consumer-health', 'Consumer Health', 'Consumer Health', 'Produk kesehatan sehari-hari yang terpercaya untuk seluruh keluarga Indonesia.', 'Everyday trusted health products for every Indonesian family.'],
            ['speciality-care', 'Speciality Care', 'Speciality Care', 'Solusi terapi khusus dengan standar mutu farmasi tertinggi.', 'Specialised therapeutic solutions with the highest pharmaceutical standards.'],
            ['nutrition-herbal', 'Nutritions and Herbal Care', 'Nutritions and Herbal Care', 'Nutrisi dan perawatan herbal alami untuk hidup lebih sehat.', 'Natural nutrition and herbal care for a healthier life.'],
        ];

        $catModels = [];

        foreach ($cats as $i => [$slug, $nId, $nEn, $dId, $dEn]) {
            $catModels[$slug] = ProductCategory::create([
                'slug' => $slug,
                'name_id' => $nId,
                'name_en' => $nEn,
                'description_id' => $dId,
                'description_en' => $dEn,
                'sort' => $i,
            ]);
        }

        $products = [
            ['consumer-health', 'OBH Combi', 'Obat batuk terpercaya keluarga Indonesia.', 'Trusted family cough medicine.'],
            ['consumer-health', 'Eye Mo', 'Tetes mata untuk mata segar sepanjang hari.', 'Eye drops for fresh eyes all day.'],
            ['consumer-health', 'Bodrexin', 'Solusi demam dan nyeri untuk anak.', 'Fever and pain relief for children.'],
            ['speciality-care', 'Combizim', 'Enzim pencernaan untuk kebutuhan spesifik.', 'Digestive enzymes for specific needs.'],
            ['speciality-care', 'Combantrin', 'Obat cacing yang efektif dan aman.', 'Effective and safe deworming treatment.'],
            ['nutrition-herbal', 'Imboost', 'Suplemen daya tahan tubuh berbahan Echinacea.', 'Echinacea-based immunity supplement.'],
            ['nutrition-herbal', 'Fitkom', 'Multivitamin herbal untuk anak aktif.', 'Herbal multivitamin for active children.'],
        ];

        foreach ($products as $i => [$catSlug, $nId, $dId, $dEn]) {
            Product::create([
                'product_category_id' => $catModels[$catSlug]->id,
                'slug' => Str::slug($nId),
                'name_id' => $nId,
                'name_en' => $nId,
                'description_id' => $dId,
                'description_en' => $dEn,
                'sort' => $i,
            ]);
        }

        // ---- Articles ----
        $articles = [
            ['Vitamin Yang Harus Ada Di Setiap Piring Sahurmu', 'Vitamins That Should Be On Every Sahur Plate', 'edukasi_gaya_hidup'],
            ['Kenali Zinc: Mineral Penting Untuk Tubuh Aktif Saat Puasa', 'Get To Know Zinc: An Essential Mineral During Fasting', 'edukasi_gaya_hidup'],
            ['Apakah Ramuan Herbal Masih Relevan Dijaman Sekarang', 'Are Herbal Remedies Still Relevant Today', 'edukasi_gaya_hidup'],
            ['Combiphar Women Empower Women Communities in Indonesia', 'Combiphar Women Empower Women Communities in Indonesia', 'pembaruan_korporasi'],
            ['Combiphar Raih Penghargaan SME Award 2024', 'Combiphar Wins SME Award 2024', 'pembaruan_korporasi'],
            ['Combiphar Perkuat Kehadiran di Pasar Global', 'Combiphar Strengthens Its Global Market Presence', 'pembaruan_korporasi'],
        ];

        foreach ($articles as $i => [$tId, $tEn, $cat]) {
            Article::create([
                'slug' => Str::slug($tId),
                'title_id' => $tId,
                'title_en' => $tEn,
                'excerpt_id' => 'Ringkasan singkat mengenai topik kesehatan dan kabar terbaru dari Combiphar.',
                'excerpt_en' => 'A short summary of the health topic and latest updates from Combiphar.',
                'body_id' => '<p>Konten lengkap artikel akan dikelola melalui CMS. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>',
                'body_en' => '<p>The full article content is managed through the CMS. Lorem ipsum dolor sit amet.</p>',
                'category' => $cat,
                'published_at' => now()->subDays($i * 3),
                'is_featured' => $i < 3,
            ]);
        }

        // ---- People (BOC / BOD) ----
        $people = [
            ['Dr. Biantoro Wanandi', 'Komisaris Utama', 'President Commissioner', 'commissioners'],
            ['Hamadi Widjaja', 'Komisaris', 'Commissioner', 'commissioners'],
            ['Michael Wanandi', 'Direktur Utama', 'President Director', 'directors'],
            ['Wely Thomas', 'Direktur Keuangan', 'Finance Director', 'directors'],
            ['Rina Kartika', 'Direktur Pemasaran', 'Marketing Director', 'directors'],
        ];

        foreach ($people as $i => [$name, $rId, $rEn, $group]) {
            Person::create([
                'name' => $name,
                'role_id' => $rId,
                'role_en' => $rEn,
                'group' => $group,
                'sort' => $i,
            ]);
        }

        // ---- Awards ----
        $awards = [
            ['Sertifikasi Good Manufacturing Practices (GMP)', 'Good Manufacturing Practices (GMP) Certification', 1996],
            ['Top Brand Award', 'Top Brand Award', 2023],
            ['SME Award kategori Women Empowerment', 'SME Award for Women Empowerment', 2024],
        ];

        foreach ($awards as $i => [$tId, $tEn, $year]) {
            Award::create([
                'title_id' => $tId,
                'title_en' => $tEn,
                'year' => $year,
                'sort' => $i,
            ]);
        }

        // ---- Milestones ----
        $milestones = [
            ['1971', 'Combiphar didirikan pada tahun 1971.', 'Combiphar was founded in 1971.'],
            ['1996', 'Memulai era baru dan meraih sertifikasi Good Manufacturing Practices (GMP).', 'A new era begins with Good Manufacturing Practices (GMP) certification.'],
            ['2012', 'Transformasi bisnis dan bermitra dengan 19 negara.', 'Business transformation and partnerships across 19 countries.'],
            ['2026', '55 tahun menemani Indonesia, Championing a Healthy Tomorrow.', '55 years alongside Indonesia, Championing a Healthy Tomorrow.'],
        ];

        foreach ($milestones as $i => [$year, $cId, $cEn]) {
            Milestone::create([
                'year' => $year,
                'caption_id' => $cId,
                'caption_en' => $cEn,
                'sort' => $i,
            ]);
        }

        // ---- Impact programs ----
        $impacts = [
            ['Eye Mo di Filipina', 'Eye Mo in the Philippines', 'Eye Mo telah menjadi merek terpercaya lintas generasi di Filipina dan meluncurkan Eye Mo Daily Care selama pandemi.', 'Eye Mo has become a trusted multi-generational brand in the Philippines and launched Eye Mo Daily Care during the pandemic.'],
            ['Combi Hope Empowerment', 'Combi Hope Empowerment', 'Memberdayakan 230 petani perempuan di Jawa Tengah dengan pelatihan budidaya herbal dan fasilitas produksi.', 'Empowering 230 women farmers in Central Java with herbal cultivation training and production facilities.'],
            ['Combi Hope Healthy Living Education', 'Combi Hope Healthy Living Education', 'Menjangkau lebih dari 20.000 siswa dengan materi kesehatan yang praktis dan relevan.', 'Reaching more than 20,000 students with practical and relevant health education.'],
        ];

        foreach ($impacts as $i => [$tId, $tEn, $bId, $bEn]) {
            ImpactProgram::create([
                'title_id' => $tId,
                'title_en' => $tEn,
                'body_id' => $bId,
                'body_en' => $bEn,
                'sort' => $i,
            ]);
        }

        // ---- CSR programs ----
        $csr = [
            ['esg', 'Environmental', 'Environmental', 'Inisiatif keberlanjutan lingkungan Combiphar.', 'Combiphar environmental sustainability initiatives.'],
            ['esg', 'Social', 'Social', 'Program pemberdayaan masyarakat dan kesehatan.', 'Community empowerment and health programs.'],
            ['esg', 'Governance', 'Governance', 'Tata kelola perusahaan yang transparan dan akuntabel.', 'Transparent and accountable corporate governance.'],
            ['health_campaign', 'Kampanye Hidup Sehat', 'Healthy Living Campaign', 'Edukasi kesehatan untuk masyarakat luas.', 'Health education for the wider community.'],
            ['sports', 'Combiphar Sports', 'Combiphar Sports', 'Mendukung gaya hidup aktif melalui olahraga.', 'Supporting an active lifestyle through sports.'],
        ];

        foreach ($csr as $i => [$cat, $tId, $tEn, $bId, $bEn]) {
            CsrProgram::create([
                'category' => $cat,
                'title_id' => $tId,
                'title_en' => $tEn,
                'body_id' => $bId,
                'body_en' => $bEn,
                'sort' => $i,
            ]);
        }

        // ---- Investor documents ----
        foreach ([2022, 2023, 2024, 2025] as $i => $year) {
            InvestorDocument::create([
                'category' => 'sustainability',
                'title_id' => "Laporan Keberlanjutan $year",
                'title_en' => "Sustainability Report $year",
                'year' => $year,
                'sort' => $i,
            ]);
        }

        foreach ([2023, 2024] as $i => $year) {
            InvestorDocument::create([
                'category' => 'annual_report',
                'title_id' => "Laporan Tahunan $year",
                'title_en' => "Annual Report $year",
                'year' => $year,
                'sort' => $i,
            ]);
        }

        // ---- Job vacancies ----
        $jobs = [
            ['Medical Representative', 'Medical Representative', 'Sales', 'Sales', 'Jakarta'],
            ['Digital Marketing Specialist', 'Digital Marketing Specialist', 'Marketing', 'Marketing', 'Bogor'],
        ];

        foreach ($jobs as $i => [$tId, $tEn, $dId, $dEn, $loc]) {
            JobVacancy::create([
                'title_id' => $tId,
                'title_en' => $tEn,
                'department_id' => $dId,
                'department_en' => $dEn,
                'location' => $loc,
                'description_id' => 'Deskripsi pekerjaan akan dikelola melalui CMS.',
                'description_en' => 'The job description is managed through the CMS.',
                'is_open' => true,
                'sort' => $i,
            ]);
        }

        // ---- Global sites ----
        $sites = [
            ['Jabodetabek', 'Indonesia', 'Kantor pusat dan fasilitas manufaktur di area Jabodetabek dan Bogor.', 'Head office and manufacturing facilities in the Greater Jakarta and Bogor area.'],
            ['Manufacturing Solutions', 'Indonesia', 'Layanan manufaktur end-to-end yang andal, berkualitas, dan scalable.', 'Reliable, high-quality and scalable end-to-end manufacturing services.'],
            ['International Business', 'Global', 'Jangkauan bisnis ke berbagai pasar global melalui jaringan distribusi.', 'Business reach across global markets through our distribution network.'],
        ];

        foreach ($sites as $i => [$name, $region, $aId, $aEn]) {
            GlobalSite::create([
                'name' => $name,
                'region' => $region,
                'address_id' => $aId,
                'address_en' => $aEn,
                'sort' => $i,
            ]);
        }

        // ---- Online shops ----
        foreach (['Tokopedia', 'Shopee', 'Blibli', 'Lazada', 'TikTok Shop', 'Orami'] as $i => $shop) {
            OnlineShop::create([
                'name' => $shop,
                'url' => 'https://www.' . strtolower(str_replace(' ', '', $shop)) . '.com',
                'sort' => $i,
            ]);
        }
    }
}