<?php

/*
 * Router script for `php artisan serve` (auto-picked from base_path by
 * ServeCommand — used on local AND the dev server, see the Dockerfile CMD).
 *
 * The PHP built-in server sends static files with NO caching headers, so every
 * page view re-downloads every image/JS/CSS (the award popup alone is dozens
 * of images). Serve known asset types ourselves with Cache-Control + ETag/304,
 * and gzip the text ones; everything else falls through to Laravel unchanged.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$file = __DIR__.'/public'.$uri;

if ($uri !== '/' && strpos($uri, '..') === false && is_file($file)) {
    $types = [
        'js' => 'application/javascript', 'css' => 'text/css',
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
        'webp' => 'image/webp', 'gif' => 'image/gif', 'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon', 'avif' => 'image/avif',
        'woff2' => 'font/woff2', 'woff' => 'font/woff', 'otf' => 'font/otf', 'ttf' => 'font/ttf',
        'mp4' => 'video/mp4', 'webm' => 'video/webm',
        'json' => 'application/json', 'map' => 'application/json',
        'txt' => 'text/plain', 'xml' => 'application/xml',
    ];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    // The PHP built-in server EXECUTES any .php handed to it, and public/storage
    // symlinks to the CMS upload tree — so one writable script under there would
    // be remote code execution. Nothing below public/ may execute except the
    // front controller required at the bottom of this file.
    if (preg_match('/^(php[0-9]?|phtml|phps|phar|pht|inc|hh)$/', $ext)) {
        http_response_code(404);
        exit;
    }

    if (isset($types[$ext])) {
        $mtime = filemtime($file);
        $size = filesize($file);
        $etag = '"'.md5($uri.$mtime.$size).'"';

        // Vite build output is content-hashed -> cache forever; the rest 1 day
        // (uploads/awards can be replaced in place -> hard refresh picks them up).
        header('Cache-Control: '.(strpos($uri, '/build/') === 0
            ? 'public, max-age=31536000, immutable'
            : 'public, max-age=86400'));
        header('ETag: '.$etag);
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $mtime).' GMT');

        if (trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
            http_response_code(304);
            exit;
        }

        header('Content-Type: '.$types[$ext]);

        $textual = in_array($ext, ['js', 'css', 'svg', 'json', 'map', 'txt', 'xml'], true);
        if ($textual && strpos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false) {
            $body = gzencode((string) file_get_contents($file), 6);
            header('Content-Encoding: gzip');
            header('Vary: Accept-Encoding');
            header('Content-Length: '.strlen($body));
            echo $body;
        } else {
            header('Content-Length: '.$size);
            readfile($file);
        }
        exit;
    }

    return false; // unknown extension — let the built-in server serve it raw
}

require_once __DIR__.'/public/index.php';
