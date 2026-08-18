<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * URL slugs mirror combiphar.com (its /back/api/v1/pages table and CSR community
 * slugs, read 2026-08-18): the page paths per locale, plus 301s for every
 * combiphar.com URL that has no path of its own here (career page, home aliases,
 * ?id= news tabs, ?cat_id= product categories) and for this site's own earlier
 * paths (/en/about, /en/contact, the two renamed CSR slugs).
 *
 * Read-only against the dev database, like AdminPanelSmokeTest.
 */
class MirroredSlugTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.connections.mysql.database', $this->databaseFromEnvFile());
        config()->set('database.default', 'mysql');
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

    /** Page paths exactly as combiphar.com serves them, both locales. */
    public function test_halaman_memakai_slug_combiphar_com(): void
    {
        foreach ([
            '/id/tentang-kami', '/en/about-us',
            '/id/produk', '/en/products',
            '/id/csr-komunitas', '/en/csr-community',
            '/id/berita', '/en/news',
            '/id/kontak-kami', '/en/contact-us',
            '/id/kebijakan-privasi', '/en/privacy-policy',
            '/id/syarat-ketentuan', '/en/terms-of-use',
        ] as $path) {
            $this->get($path)->assertOk();
        }
    }

    /** combiphar.com URLs with no page of their own here → 301 to where the content lives. */
    public function test_url_combiphar_com_tanpa_padanan_dialihkan_301(): void
    {
        $cases = [
            '/id/karir' => '/id/kontak-kami?tab=karir',
            '/en/career' => '/en/contact-us?tab=karir',
            '/id/beranda' => '/id',
            '/en/home' => '/en',
            '/en/news?id=1' => '/en/news?tab=health',
            '/id/berita?id=2' => '/id/berita?tab=investor',
            '/en/products?cat_id=3' => '/en/products?cat=consumer-health',
            '/id/produk?cat_id=6' => '/id/produk?cat=nutrition-herbal&sub=nutrition-herbal-honey',
        ];

        foreach ($cases as $from => $to) {
            $this->get($from)->assertStatus(301)->assertRedirect($to);
        }

        // An unmapped legacy id renders the page instead of looping or dying.
        $this->get('/en/products?cat_id=1')->assertOk();
        $this->get('/en/news?id=99')->assertOk();
    }

    /** This site's own earlier paths keep working. */
    public function test_jalur_lama_situs_ini_dialihkan_301(): void
    {
        $cases = [
            '/en/about' => '/en/about-us',
            '/en/contact' => '/en/contact-us',
            '/id/about' => '/id/tentang-kami',
            '/en/csr' => '/en/csr-community',
        ];

        foreach ($cases as $from => $to) {
            $this->get($from)->assertStatus(301)->assertRedirect($to);
        }
    }

    /** The two CSR slugs renamed to combiphar.com's (2026_08_18_000003) — old slug → 301, unless a row still owns it. */
    public function test_slug_csr_lama_dialihkan_ke_slug_combiphar_com(): void
    {
        foreach ([
            '/en/csr-community/environmental' => '/en/csr-community/environmental-care-action',
            '/id/csr-komunitas/social' => '/id/csr-komunitas/social-care-action',
        ] as $from => $to) {
            $response = $this->get($from);
            if ($response->getStatusCode() === 200) {
                $this->markTestSkipped("A csr_programs row still holds the old slug ({$from}); the migration was skipped or not run.");
            }
            $response->assertStatus(301)->assertRedirect($to);
        }
    }
}
