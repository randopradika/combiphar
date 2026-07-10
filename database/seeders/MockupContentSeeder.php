<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Award;
use App\Models\GlobalSite;
use App\Models\ImpactProgram;
use App\Models\Milestone;
use App\Models\Page;
use App\Models\Person;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class MockupContentSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Home page media ----
        Page::where('slug', 'home')->update([
            'hero_image' => 'seed/hero/keluarga-langit.jpg',
            'manifesto_image' => 'seed/manifesto/pasangan-langit.jpg',
            'cta_image' => 'seed/cta/tim-berjalan.jpg',
        ]);

        // ---- About page banner ----
        Page::where('slug', 'about')->update([
            'banner_image' => 'seed/about/hero-banner.jpg',
        ]);

        // ---- Board members (11 real, with photos) ----
        Schema::disableForeignKeyConstraints();
        Person::query()->delete();
        Schema::enableForeignKeyConstraints();

        $commissioners = [
            ['Dr. Willem Biantoro Wanandi', 'Presiden Komisaris', 'President Commissioner', 'biantoro-wanandi.jpg'],
            ['Hamadi Widjaja', 'Komisaris', 'Commissioner', 'hamadi-widjaja.jpg'],
            ['Roy Sparringa', 'Komisaris Independen', 'Independent Commissioner', 'roy-sparringa.jpg'],
            ['Francis Wanandi', 'Komisaris', 'Commissioner', 'francis-wanandi.jpg'],
            ['Felicia Wanandi', 'Komisaris', 'Commissioner', 'felicia-wanandi.jpg'],
        ];

        foreach ($commissioners as $i => [$n, $ri, $re, $img]) {
            Person::create([
                'name' => $n,
                'role_id' => $ri,
                'role_en' => $re,
                'group' => 'commissioners',
                'photo' => 'seed/board/' . $img,
                'sort' => $i,
                'bio_id' => 'Profil singkat ' . $n . ' di Combiphar Group.',
                'bio_en' => 'A brief profile of ' . $n . ' at Combiphar Group.',
            ]);
        }

        $directors = [
            ['Michael Wanadi', 'Presiden Direktur', 'President Director', 'michael-wanadi.jpg'],
            ['Lim Soeyantho', 'Direktur', 'Director', 'lim-soeyantho.jpg'],
            ['Petrus Gunadi', 'Direktur', 'Director', 'petrus-gunadi.jpg'],
            ['Weitarsa Hendarto', 'Direktur', 'Director', 'weitarsa-hendarto.jpg'],
            ['Arief Effendy', 'Direktur', 'Director', 'arief-effendy.jpg'],
            ['Delano Lusikooy', 'Direktur', 'Director', 'delano-lusikooy.jpg'],
        ];

        foreach ($directors as $i => [$n, $ri, $re, $img]) {
            Person::create([
                'name' => $n,
                'role_id' => $ri,
                'role_en' => $re,
                'group' => 'directors',
                'photo' => 'seed/board/' . $img,
                'sort' => $i,
                'bio_id' => 'Profil singkat ' . $n . ' di Combiphar Group.',
                'bio_en' => 'A brief profile of ' . $n . ' at Combiphar Group.',
            ]);
        }

        // ---- Milestones (home journey, with photos) ----
        Schema::disableForeignKeyConstraints();
        Milestone::query()->delete();
        Schema::enableForeignKeyConstraints();

        $ms = [
            ['1971', 'Combiphar didirikan pada 1971.', 'Combiphar was founded in 1971.', 'seed/milestone/pabrik-1971.jpg'],
            ['1996', 'Memulai era baru bersama Dr. Biantoro Wanandi dan Hamadi Widjaja, meraih sertifikasi Good Manufacturing Practices (GMP).', 'A new era begins with Dr. Biantoro Wanandi and Hamadi Widjaja, earning GMP certification.', 'seed/milestone/era-gmp.jpg'],
            ['2012', 'Transformasi bisnis, bermitra dengan 19 negara dan memperkuat portofolio melalui akuisisi.', 'Business transformation, partnering with 19 countries and strengthening the portfolio through acquisitions.', 'seed/milestone/transformasi.jpg'],
            ['2026', '55 tahun menemani Indonesia, Championing a Healthy Tomorrow.', '55 years alongside Indonesia, Championing a Healthy Tomorrow.', null],
        ];

        foreach ($ms as $i => [$y, $ci, $ce, $img]) {
            Milestone::create([
                'year' => $y,
                'caption_id' => $ci,
                'caption_en' => $ce,
                'photo' => $img,
                'sort' => $i,
            ]);
        }

        // ---- Impact programs (with photos) ----
        Schema::disableForeignKeyConstraints();
        ImpactProgram::query()->delete();
        Schema::enableForeignKeyConstraints();

        $imp = [
            ['Eye Mo di Filipina', 'Eye Mo in the Philippines', 'Eye Mo telah menjadi merek terpercaya lintas generasi di Filipina. Selama pandemi, mereka meluncurkan Eye Mo Daily Care untuk mengedukasi jutaan warga bahwa menjaga kebersihan mata adalah bagian krusial dari perlindungan diri sehari-hari.', 'Eye Mo has become a trusted multi-generational brand in the Philippines, launching Eye Mo Daily Care during the pandemic.', 'seed/impact/eye-mo-filipina.jpg'],
            ['Combi Hope Empowerment', 'Combi Hope Empowerment', 'Melalui program ini, Combiphar memberdayakan 230 petani perempuan di Jawa Tengah dengan pelatihan budidaya herbal dan fasilitas produksi. Inisiatif ini meraih penghargaan SME Award 2024 kategori Women Empowerment.', 'This program empowers 230 women farmers in Central Java with herbal cultivation training and production facilities, winning the SME Award 2024 for Women Empowerment.', 'seed/impact/combi-hope-empowerment.jpg'],
            ['Combi Hope Healthy Living Education', 'Combi Hope Healthy Living Education', 'Kami membangun kesadaran hidup sehat sejak dini dengan menjangkau lebih dari 20.000 siswa di berbagai sekolah di Jawa, Bali, dan sekitar area operasional kami di Bogor.', 'We build early awareness of healthy living, reaching more than 20,000 students across schools in Java, Bali, and near our Bogor operations.', null],
        ];

        foreach ($imp as $i => [$ti, $te, $bi, $be, $img]) {
            ImpactProgram::create([
                'title_id' => $ti,
                'title_en' => $te,
                'body_id' => $bi,
                'body_en' => $be,
                'image' => $img,
                'sort' => $i,
            ]);
        }

        // ---- Product category images ----
        ProductCategory::where('slug', 'consumer-health')->update([
            'image' => 'seed/product-consumer-health.png',
        ]);

        ProductCategory::where('slug', 'speciality-care')->update([
            'image' => 'seed/product-specialty-care.png',
        ]);

        ProductCategory::where('slug', 'nutrition-herbal')->update([
            'image' => 'seed/product-nutrition-herbal-care.png',
        ]);

        // ---- News cover images (health articles) ----
        $imgs = [
            'seed/news/piring-sahur.jpg',
            'seed/news/zinc.jpg',
            'seed/news/minuman-herbal.jpg',
        ];

        foreach (Article::where('category', 'edukasi_gaya_hidup')->orderBy('id')->get() as $i => $a) {
            if (isset($imgs[$i])) {
                $a->update([
                    'cover_image' => $imgs[$i],
                ]);
            }
        }

        // ---- Awards (7 logos) ----
        Schema::disableForeignKeyConstraints();
        Award::query()->delete();
        Schema::enableForeignKeyConstraints();

        for ($i = 1; $i <= 7; $i++) {
            Award::create([
                'title_id' => 'Penghargaan ' . $i,
                'title_en' => 'Award ' . $i,
                'image' => 'seed/awards/award-' . $i . '.png',
                'sort' => $i,
            ]);
        }

        // ---- Global site images ----
        GlobalSite::where('name', 'Manufacturing Solutions')->update([
            'image' => 'seed/about/manufacturing.jpg',
        ]);

        GlobalSite::where('name', 'International Business')->update([
            'image' => 'seed/about/international.jpg',
        ]);
    }
}