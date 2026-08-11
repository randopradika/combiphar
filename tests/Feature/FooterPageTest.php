<?php

namespace Tests\Feature;

use App\Filament\Pages\Footer;
use App\Models\FooterSetting;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Layar Halaman -> Footer memakai Page, bukan Resource, sehingga jalur
 * simpannya ditulis sendiri dan tidak tersentuh AdminPanelSmokeTest -- test itu
 * hanya membuka halamannya (GET).
 *
 * Yang paling mudah rusak di sini adalah bentuk state Repeater: Filament
 * menyimpan itemnya dalam array berkunci UUID, jadi bila dehidrasinya tidak
 * berjalan kolom json logos akan berisi objek berkunci UUID, bukan daftar
 * berurut -- dan urutan logo di footer diam-diam ikut ditentukan kunci itu.
 *
 * Memakai basis data dev (sama seperti AdminPanelSmokeTest), maka nilai awalnya
 * dikembalikan di akhir test agar isi CMS tidak berubah karena menjalankan test.
 */
class FooterPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.connections.mysql.database', $this->databaseFromEnvFile());
        config()->set('database.default', 'mysql');

        $admin = User::query()->where('role', 'admin')->first();

        if (! $admin) {
            $this->markTestSkipped('Tidak ada pengguna admin di database ini.');
        }

        $this->actingAs($admin);
    }

    private function databaseFromEnvFile(): string
    {
        $path = base_path('.env');

        if (is_readable($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (str_starts_with(trim($line), 'DB_DATABASE=')) {
                    return trim(explode('=', $line, 2)[1], " \t\"'");
                }
            }
        }

        return 'combiphar';
    }

    public function test_formulir_terisi_dari_basis_data(): void
    {
        $footer = FooterSetting::current();

        Livewire::test(Footer::class)
            ->assertFormSet([
                'follow_label_id' => $footer->follow_label_id,
                'copyright_id' => $footer->copyright_id,
            ]);
    }

    public function test_menyimpan_menulis_kembali_dan_logo_tetap_daftar_berurut(): void
    {
        $footer = FooterSetting::current();
        $original = $footer->only(['follow_label_id', 'follow_label_en', 'copyright_id', 'copyright_en', 'logos']);

        try {
            Livewire::test(Footer::class)
                // Hanya labelnya yang diubah: logo sengaja dibiarkan seperti
                // hasil hidrasi dari basis data, karena justru itu yang diuji --
                // menyimpan perubahan APA PUN tidak boleh mengacak bentuk kolom
                // logos. Mengisi FileUpload dengan jalur karangan tidak mungkin:
                // berkasnya tidak ada, sehingga validasinya menolak.
                ->fillForm(['follow_label_id' => 'Ikuti kami (uji):'])
                ->call('save')
                ->assertHasNoFormErrors();

            $saved = FooterSetting::current()->fresh();

            $this->assertSame('Ikuti kami (uji):', $saved->follow_label_id);
            // Berkunci 0,1,2... -- bukan UUID: urutan logo di footer berasal
            // dari urutan daftar ini.
            $this->assertSame(
                range(0, count($original['logos'] ?? []) - 1),
                array_keys($saved->logos ?? []),
            );
            $this->assertSame(
                array_column($original['logos'] ?? [], 'image'),
                array_column($saved->logos ?? [], 'image'),
            );
        } finally {
            FooterSetting::current()->update($original);
        }
    }
}
