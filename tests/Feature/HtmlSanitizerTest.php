<?php

namespace Tests\Feature;

use App\Support\Html;
use Tests\TestCase;

/**
 * App\Support\Html::clean() — pagar server-side untuk semua teks kaya CMS
 * yang dirender lewat dangerouslySetInnerHTML (temuan codeReview "Stored XSS
 * in dangerouslySetInnerHTML with CSP Bypass"). Feature test (bukan Unit)
 * karena purifier butuh aplikasi ter-boot untuk storage_path().
 */
class HtmlSanitizerTest extends TestCase
{
    public function test_script_dan_event_handler_dibuang(): void
    {
        $dirty = '<p onmouseover="alert(1)">Halo</p>'
            .'<script>alert(document.cookie)</script>'
            .'<img src="/img/x.png" onerror="alert(1)">';

        $clean = Html::clean($dirty);

        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('onmouseover', $clean);
        $this->assertStringNotContainsString('alert', $clean);
        $this->assertStringContainsString('Halo', $clean);
    }

    public function test_url_javascript_dan_iframe_dibuang(): void
    {
        $clean = Html::clean(
            '<a href="javascript:alert(1)">klik</a><iframe src="https://evil.example"></iframe>'
        );

        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('<iframe', $clean);
    }

    public function test_markup_kaya_yang_sah_dipertahankan(): void
    {
        $html = '<p><strong>Tebal</strong> dan <em>miring</em> serta '
            .'<a href="https://example.com" target="_blank">tautan</a>.</p>'
            .'<ul><li>satu</li><li>dua</li></ul>';

        $clean = Html::clean($html);

        $this->assertStringContainsString('<strong>Tebal</strong>', $clean);
        $this->assertStringContainsString('<em>miring</em>', $clean);
        $this->assertStringContainsString('href="https://example.com"', $clean);
        $this->assertStringContainsString('<li>satu</li>', $clean);
    }

    public function test_nilai_kosong_lewat_apa_adanya(): void
    {
        $this->assertNull(Html::clean(null));
        $this->assertSame('', Html::clean(''));
    }
}
