<?php

namespace Tests\Feature;

use App\Models\Article;
use Tests\TestCase;

/**
 * Slug artikel per bahasa (meniru combiphar.com): /id/berita/{slug} dan
 * /en/news/{slug_en}. Slug bahasa lain tetap dikenali tetapi dialihkan 301 ke
 * slug kanonis locale-nya, dan altUrls (tombol bahasa, canonical, hreflang)
 * memasangkan slug yang benar per bahasa.
 *
 * Memakai basis data dev (seperti AdminPanelSmokeTest): satu artikel sementara
 * dibuat dengan slug yang mustahil bentrok dan dihapus lagi di tearDown.
 */
class NewsSlugTest extends TestCase
{
    private const SLUG_ID = 'zz-test-slug-artikel-uji-otomatis';

    private const SLUG_EN = 'zz-test-slug-automated-test-article';

    private ?Article $article = null;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.connections.mysql.database', $this->databaseFromEnvFile());
        config()->set('database.default', 'mysql');

        Article::whereIn('slug', [self::SLUG_ID, self::SLUG_EN])->delete();

        $this->article = Article::create([
            'slug' => self::SLUG_ID,
            'slug_en' => self::SLUG_EN,
            'title_id' => 'Artikel uji otomatis',
            'title_en' => 'Automated test article',
            'body_id' => '<p>isi</p>',
            'body_en' => '<p>body</p>',
            'category' => 'lainnya',
            'published_at' => now()->subDay(),
        ]);
    }

    protected function tearDown(): void
    {
        $this->article?->delete();

        parent::tearDown();
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

    public function test_setiap_bahasa_memakai_slugnya_sendiri(): void
    {
        $this->get('/id/berita/'.self::SLUG_ID)->assertOk();
        $this->get('/en/news/'.self::SLUG_EN)->assertOk();
    }

    public function test_slug_bahasa_lain_dialihkan_301_ke_slug_kanonis(): void
    {
        $this->get('/en/news/'.self::SLUG_ID)
            ->assertStatus(301)
            ->assertRedirect('/en/news/'.self::SLUG_EN);

        $this->get('/id/berita/'.self::SLUG_EN)
            ->assertStatus(301)
            ->assertRedirect('/id/berita/'.self::SLUG_ID);

        // Query string ikut terbawa (utm, tab, dsb.).
        $this->get('/en/news/'.self::SLUG_ID.'?utm_source=x')
            ->assertRedirect('/en/news/'.self::SLUG_EN.'?utm_source=x');
    }

    public function test_alt_urls_memasangkan_slug_per_bahasa(): void
    {
        $expected = [
            'id' => url('/id/berita/'.self::SLUG_ID),
            'en' => url('/en/news/'.self::SLUG_EN),
        ];

        $this->get('/en/news/'.self::SLUG_EN)->assertInertia(fn ($page) => $page
            ->component('NewsDetail')
            ->where('altUrls', $expected)
            ->where('article.url', $expected['en']));

        $this->get('/id/berita/'.self::SLUG_ID)->assertInertia(fn ($page) => $page
            ->where('altUrls', $expected)
            ->where('article.url', $expected['id']));
    }

    public function test_tanpa_slug_en_halaman_en_memakai_slug_indonesia(): void
    {
        $this->article->update(['slug_en' => null]);

        $this->get('/en/news/'.self::SLUG_ID)->assertOk()
            ->assertInertia(fn ($page) => $page->where('article.url', url('/en/news/'.self::SLUG_ID)));
    }

    public function test_slug_tak_dikenal_tetap_404(): void
    {
        $this->get('/en/news/zz-test-slug-yang-tidak-ada')->assertNotFound();
    }
}
