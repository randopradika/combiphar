<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu baris berisi seluruh isi footer.
     *
     * Sebelumnya isi footer tersebar dan sebagian besar tidak bisa disunting:
     * teks copyright terkubur di dalam record halaman Beranda (tempat yang tidak
     * akan dicari siapa pun), label "Ikuti kami" tertanam di berkas bahasa, dan
     * kedua logo di kanan bawah ditulis langsung di SiteLayout.jsx sehingga
     * menggantinya berarti deploy. Semuanya pindah ke satu layar "Footer".
     *
     * Logonya disalin dari public/img ke disk publik supaya baris CMS-nya
     * menunjuk ke berkas sungguhan sejak awal: dengan begitu JSX tidak perlu
     * cadangan tertanam, dan mengosongkan bidangnya benar-benar menghilangkan
     * logo (CLAUDE.md §9) alih-alih memunculkan kembali versi lama.
     */
    private const LOGOS = [
        // Tinggi dari Figma 577:3316 pada lebar 1920.
        ['file' => 'logo-combiphar-white.svg', 'alt' => 'Combiphar', 'height' => 82],
        ['file' => 'logo-combicare-white.svg', 'alt' => 'Combi Care Center', 'height' => 89],
    ];

    public function up(): void
    {
        Schema::create('footer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('follow_label_id')->nullable();
            $table->string('follow_label_en')->nullable();
            // text, bukan string: copyright adalah RichEditor -- "Combiphar"
            // tercetak tebal di desain (.footer__rights strong).
            $table->text('copyright_id')->nullable();
            $table->text('copyright_en')->nullable();
            $table->json('logos')->nullable();
            $table->timestamps();
        });

        $page = DB::table('pages')->where('slug', 'home')->first();

        DB::table('footer_settings')->insert([
            'follow_label_id' => 'Ikuti kami di media sosial:',
            'follow_label_en' => 'Follow us on social media:',
            'copyright_id' => $page->footer_copyright_id ?? '<p>Hak Cipta Dilindungi <strong>Combiphar</strong></p>',
            'copyright_en' => $page->footer_copyright_en ?? '<p>All Rights Reserved to <strong>Combiphar</strong></p>',
            'logos' => json_encode($this->copyLogos()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['footer_copyright_id', 'footer_copyright_en']);
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->text('footer_copyright_id')->nullable();
            $table->text('footer_copyright_en')->nullable();
        });

        $footer = DB::table('footer_settings')->orderBy('id')->first();

        if ($footer) {
            DB::table('pages')->where('slug', 'home')->update([
                'footer_copyright_id' => $footer->copyright_id,
                'footer_copyright_en' => $footer->copyright_en,
            ]);
        }

        Schema::dropIfExists('footer_settings');
    }

    /**
     * Menyalin logo bawaan ke disk publik, melewati berkas yang tidak ada supaya
     * migrasi tidak pernah gagal hanya karena satu aset hilang.
     *
     * @return list<array{image: string, alt: string, height: int}>
     */
    private function copyLogos(): array
    {
        $target = storage_path('app/public/footer');
        File::ensureDirectoryExists($target);

        $logos = [];

        foreach (self::LOGOS as $logo) {
            $source = public_path('img/'.$logo['file']);

            if (! File::exists($source)) {
                continue;
            }

            if (! File::exists($target.'/'.$logo['file'])) {
                File::copy($source, $target.'/'.$logo['file']);
            }

            $logos[] = [
                'image' => 'footer/'.$logo['file'],
                'alt' => $logo['alt'],
                'height' => $logo['height'],
            ];
        }

        return $logos;
    }
};
