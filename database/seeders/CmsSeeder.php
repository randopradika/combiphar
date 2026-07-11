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

        // Figma banner tagline + intro copy for the About page.
        Page::where('slug', 'about')->update([
            'banner_title_id' => 'Championing a', 'banner_title_en' => 'Championing a',
            'banner_title2_id' => 'Healthy Tomorrow', 'banner_title2_en' => 'Healthy Tomorrow',
            'banner_subtitle_id' => 'Dengan pengalaman lebih dari 53 tahun di industri farmasi, ditandai dengan pertumbuhan berkelanjutan dan akuisisi berbagai perusahaan besar, Combiphar mewakili tujuan:',
            'banner_subtitle_en' => 'With over 53 years of professional experience in the pharmaceutical industry, marked by sustainable growth and the acquisition of major companies, Combiphar represents the purpose of:',
            'intro_id' => 'Sejak 1971, Combiphar telah menjadi bagian dari kehidupan sehari-hari masyarakat melalui ragam produk kesehatan dan setia menemani perjalanan hidup sehat, melalui solusi kesehatan terintegrasi mulai dari pencegahan, pemulihan, hingga gaya hidup sehat. Berakar pada kepedulian yang tulus dan kepercayaan yang tak lekang oleh waktu, kami terus bertumbuh bersama Indonesia.',
            'intro_en' => 'Since 1971, Combiphar has been part of people’s everyday lives through a wide range of health products, faithfully accompanying the journey to a healthier life through integrated health solutions — from prevention and recovery to a healthy lifestyle. Rooted in genuine care and enduring trust, we continue to grow together with Indonesia.',
            'vision_id' => 'Visi Combiphar adalah menjadi perusahaan terkemuka di bidang perawatan kesehatan yang menyentuh kehidupan konsumen melalui peningkatan kesehatan individu dan komunitas.',
            'vision_en' => 'Combiphar’s vision is to become a leading healthcare company that touches consumers’ lives by improving the health of individuals and communities.',
            'mission_id' => 'Mengupayakan tingkat kualitas kesehatan konsumen yang lebih baik melalui produk dan layanan berkualitas serta terjangkau dengan mempromosikan gaya hidup sehat.',
            'mission_en' => 'To improve consumers’ health and quality of life through quality, affordable products and services while promoting a healthy lifestyle.',
            'values_id' => 'AIM: Antusias dan cerdas dalam mencapai hasil, Integritas, kerjasama sinergis & harmonis, Memenangkan hati pelanggan.',
            'values_en' => 'AIM: Ambitious and smart in achieving results, Integrity and synergistic, harmonious teamwork, Meeting the hearts of our customers.',
        ]);

        // ---- Home page (hero / manifesto / CTA) ----
        // Uses the hero/CTA fields instead of a banner; images are uploaded via the CMS.
        Page::create([
            'slug' => 'home',
            'meta_title_id' => 'Combiphar - Championing a Healthy Tomorrow',
            'meta_title_en' => 'Combiphar - Championing a Healthy Tomorrow',
            'hero_line1_id' => 'Championing a',
            'hero_line1_en' => 'Championing a',
            'hero_line2_id' => 'Healthy Tomorrow',
            'hero_line2_en' => 'Healthy Tomorrow',
            'manifesto_title_id' => 'Kami percaya kesehatan adalah hak setiap orang.',
            'manifesto_title_en' => "We believe health is everyone's right.",
            'cta_title_id' => 'Mari wujudkan hari esok yang lebih sehat bersama Combiphar.',
            'cta_title_en' => "Let's build a healthier tomorrow together with Combiphar.",
        ]);

        // ---- About history (Sejarah) — separate content from Home milestones ----
        \App\Models\AboutHistory::query()->delete();
        foreach ([
            ['1971', 'seed/milestone/pabrik-1971.jpg',
                'Combiphar didirikan pada tahun 1971 dan tumbuh menjadi perusahaan perawatan kesehatan tepercaya di Indonesia.',
                'Combiphar was founded in 1971 and grew into a trusted healthcare company in Indonesia.'],
            ['2012-2017', 'seed/milestone/transformasi.jpg',
                'Combiphar melakukan transformasi bisnis, bermitra dengan 19 negara. Combiphar mengakuisisi Insto dan Eye Mo dari GSK dan mulai memasuki pasar internasional di Filipina, Singapura, Malaysia, dan Kamboja. Combiphar juga membangun pabrik biosimilar di tahun 2015.',
                'Combiphar underwent a business transformation, partnering with 19 countries. It acquired Insto and Eye Mo from GSK and entered international markets in the Philippines, Singapore, Malaysia, and Cambodia, and built a biosimilar plant in 2015.'],
            ['2019', 'seed/milestone/era-gmp.jpg',
                'Combiphar mengakuisisi Air Mancur Grup untuk lebih menekankan komitmennya Championing A Healthy Tomorrow. Pada tahun yang sama, Combiphar juga mulai memasuki pasar Hong Kong dan Makau.',
                'Combiphar acquired the Air Mancur Group to strengthen its commitment to Championing A Healthy Tomorrow. In the same year, it entered the Hong Kong and Macau markets.'],
        ] as $i => [$y, $ph, $cid, $cen]) {
            \App\Models\AboutHistory::create(['year' => $y, 'photo' => $ph, 'caption_id' => $cid, 'caption_en' => $cen, 'sort' => $i]);
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