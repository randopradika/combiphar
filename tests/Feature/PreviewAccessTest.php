<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Gerbang pratinjau konten draf (PageController::previewing()).
 *
 * Regresi untuk temuan codeReview "Authorization Bypass in Preview Mode":
 * dulu auth()->check() saja sudah meloloskan pratinjau, sehingga akun yang
 * perannya 'disabled' — justru yang aksesnya sedang dicabut — masih bisa
 * membaca artikel draf selama sesinya hidup. Kini pratinjau menuntut peran
 * CMS (admin/editor) atau tautan bertanda tangan.
 *
 * Memakai basis data dev (pola NewsSlugTest): baris uji berawalan zz-test
 * dibuat di setUp dan dihapus lagi di tearDown.
 */
class PreviewAccessTest extends TestCase
{
    private const SLUG = 'zz-test-artikel-draf-uji-pratinjau';

    private const EMAIL_EDITOR = 'zz-test-editor@example.test';

    private const EMAIL_DISABLED = 'zz-test-disabled@example.test';

    private ?Article $article = null;

    /** @var User[] */
    private array $users = [];

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.connections.mysql.database', $this->databaseFromEnvFile());
        config()->set('database.default', 'mysql');

        Article::where('slug', self::SLUG)->delete();
        User::whereIn('email', [self::EMAIL_EDITOR, self::EMAIL_DISABLED])->delete();

        $this->article = Article::create([
            'slug' => self::SLUG,
            'title_id' => 'Artikel draf uji otomatis',
            'title_en' => 'Automated draft test article',
            'body_id' => '<p>isi</p>',
            'body_en' => '<p>body</p>',
            'category' => 'lainnya',
            'published_at' => null,
        ]);

        foreach ([self::EMAIL_EDITOR => User::ROLE_EDITOR, self::EMAIL_DISABLED => User::ROLE_DISABLED] as $email => $role) {
            $user = User::create([
                'name' => 'Akun uji '.$role,
                'email' => $email,
                'password' => 'Uji-otomatis-123!',
            ]);
            // `role` sengaja di luar #[Fillable] (lihat App\Models\User).
            $user->forceFill(['role' => $role])->save();
            $this->users[$role] = $user;
        }
    }

    protected function tearDown(): void
    {
        $this->article?->delete();
        foreach ($this->users as $user) {
            $user->delete();
        }

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

    public function test_tamu_mendapat_404_untuk_artikel_draf(): void
    {
        $this->get('/id/berita/'.self::SLUG)->assertNotFound();
    }

    public function test_akun_nonaktif_juga_mendapat_404(): void
    {
        $this->actingAs($this->users[User::ROLE_DISABLED])
            ->get('/id/berita/'.self::SLUG)
            ->assertNotFound();
    }

    public function test_editor_boleh_melihat_draf(): void
    {
        $this->actingAs($this->users[User::ROLE_EDITOR])
            ->get('/id/berita/'.self::SLUG)
            ->assertOk();
    }

    public function test_tautan_bertanda_tangan_boleh_melihat_draf(): void
    {
        $url = URL::temporarySignedRoute('news.show.id', now()->addHour(), ['slug' => self::SLUG]);

        $this->get($url)->assertOk();
    }
}
