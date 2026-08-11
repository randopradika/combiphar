<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Tests\TestCase;

/**
 * Setiap halaman daftar di panel admin harus terbuka tanpa error.
 *
 * Ditulis saat tampilan galeri ditambahkan: kolom yang dibangun lewat
 * Layout\Stack dan contentGrid baru gagal ketika halamannya benar-benar
 * dirender, bukan saat php -l dijalankan -- dan panel ini punya 29 halaman,
 * terlalu banyak untuk dibuka satu per satu setiap kali tabel disentuh.
 *
 * Memakai database dev (mysql), bukan sqlite :memory: dari phpunit.xml: seluruh
 * pemeriksaan di sini hanya GET, tidak ada yang menulis, sedangkan menjalankan
 * ulang seluruh migrasi termasuk migrasi data hanya untuk membuka halaman jauh
 * lebih lambat dan lebih rapuh.
 */
class AdminPanelSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Koneksi dibuat malas, jadi mengubahnya di sini masih berlaku.
        // ⚠️ Nama database harus dibaca ulang dari .env: phpunit.xml memasang
        // DB_DATABASE=":memory:" untuk sqlite, dan nilai itu ikut terbawa ke
        // koneksi mysql sehingga querynya mencari database bernama ":memory:".
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

    public function test_dashboard_terbuka(): void
    {
        $this->get(Dashboard::getUrl(panel: 'admin'))->assertSuccessful();
    }

    /**
     * Diambil dari registry panel, bukan daftar manual, supaya resource baru
     * ikut teruji tanpa menyentuh berkas ini.
     */
    public function test_setiap_daftar_resource_terbuka(): void
    {
        $panel = Filament::getPanel('admin');
        $checked = 0;

        foreach ($panel->getResources() as $resource) {
            if (! $resource::hasPage('index')) {
                continue;
            }

            $this->get($resource::getUrl('index', panel: 'admin'))
                ->assertSuccessful();

            $checked++;
        }

        $this->assertGreaterThan(20, $checked, 'Resource yang teruji jauh lebih sedikit dari yang terdaftar.');
    }

    /**
     * Formulir hanya gagal ketika benar-benar dirender: komponen tata letak yang
     * salah susun, enum yang belum diimpor, atau closure yang meminta argumen
     * yang tidak pernah diberikan semuanya lolos dari php -l. Halaman "buat"
     * adalah cara termurah merender setiap form() sekali.
     */
    public function test_setiap_form_create_terbuka(): void
    {
        $panel = Filament::getPanel('admin');
        $checked = 0;

        foreach ($panel->getResources() as $resource) {
            if (! $resource::hasPage('create') || ! $resource::canCreate()) {
                continue;
            }

            $this->get($resource::getUrl('create', panel: 'admin'))
                ->assertSuccessful();

            $checked++;
        }

        $this->assertGreaterThan(15, $checked, 'Formulir yang teruji jauh lebih sedikit dari yang terdaftar.');
    }

    public function test_setiap_halaman_kustom_terbuka(): void
    {
        $panel = Filament::getPanel('admin');

        foreach ($panel->getPages() as $page) {
            $this->get($page::getUrl(panel: 'admin'))->assertSuccessful();
        }
    }
}
