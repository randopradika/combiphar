<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\File;

/**
 * Server-side sanitizer for admin-authored rich text.
 *
 * Every string that reaches a dangerouslySetInnerHTML sink in React passes
 * through clean() at the controller/share boundary, so both the SSR pass and
 * the client render receive HTML that has already lost any script, event
 * handler or javascript: URL. The CSP remains the second layer — this exists
 * so a compromised or malicious CMS account cannot store a payload that a
 * future CSP regression (or a nonce-stealing bypass) would detonate.
 */
class Html
{
    private static ?HTMLPurifier $purifier = null;

    /**
     * Tag/attribute allow-list covering what Filament's RichEditor emits plus
     * the markup carried by the articles imported from combiphar.com. Unknown
     * tags are dropped but their text content survives, so legacy content
     * degrades to plain text instead of disappearing.
     */
    private const ALLOWED = 'p,br,b,strong,i,em,u,s,del,strike,a[href|title|target],'
        .'ul,ol,li,h1,h2,h3,h4,h5,h6,blockquote,pre,code,span,div,hr,'
        .'img[src|alt|width|height],table,thead,tbody,tfoot,tr,th,td';

    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        return self::purifier()->purify($html);
    }

    private static function purifier(): HTMLPurifier
    {
        if (self::$purifier) {
            return self::$purifier;
        }

        // The definition cache is what keeps purify() cheap per request.
        $cacheDir = storage_path('app/purifier');
        File::ensureDirectoryExists($cacheDir);

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', $cacheDir);
        $config->set('HTML.Allowed', self::ALLOWED);
        // RichEditor links may open in a new tab; every other target is noise.
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.TargetNoopener', true);
        $config->set('HTML.TargetNoreferrer', true);

        return self::$purifier = new HTMLPurifier($config);
    }
}
