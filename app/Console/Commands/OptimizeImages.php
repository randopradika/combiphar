<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Shrink oversized CMS images in storage/app/public in place: resize anything
 * wider than --max-width, re-encode JPEGs, and convert *opaque* PNGs (photos
 * exported as PNG — routinely 10-20x larger than needed) to JPEG, updating
 * every DB reference to the renamed file. Storage is gitignored, so run this
 * once per environment (local + dev server) after big content imports.
 *
 *   php artisan images:optimize --dry-run
 *   php artisan images:optimize
 */
class OptimizeImages extends Command
{
    protected $signature = 'images:optimize
        {--dry-run : Report what would change without touching files}
        {--max-width=1920 : Resize images whose longest side exceeds this}
        {--min-kb=400 : Skip files smaller than this}
        {--dir= : Only scan this subdirectory of storage/app/public}
        {--no-convert : Never convert PNG to JPEG (keeps every filename, so no DB refs change)}';

    protected $description = 'Resize + recompress oversized images in storage/app/public (opaque PNGs become JPEG, DB refs updated)';

    private const JPEG_QUALITY = 82;

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $dry = (bool) $this->option('dry-run');
        $maxW = (int) $this->option('max-width');
        $minBytes = (int) $this->option('min-kb') * 1024;

        $totalBefore = $totalAfter = $count = 0;

        foreach ($disk->allFiles($this->option('dir') ?: null) as $rel) {
            $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
            if (! in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
                continue;
            }

            $abs = $disk->path($rel);
            $size = filesize($abs);
            if ($size < $minBytes) {
                continue;
            }

            // Decode by content, not extension — CMS uploads include PNGs
            // saved under a .jpg name, which imagecreatefromjpeg() rejects
            // as unreadable (dev's 1.5 MB milestone slide, several seeds).
            $im = @imagecreatefromstring((string) file_get_contents($abs));
            if (! $im) {
                $this->warn("unreadable, skipped: {$rel}");

                continue;
            }
            $info = @getimagesize($abs);
            $isPngBytes = ($info[2] ?? null) === IMAGETYPE_PNG;

            [$w, $h] = [imagesx($im), imagesy($im)];
            // Bound the LONGEST side, not just width — portrait uploads
            // (1080x1920 certificates etc.) are just as oversized.
            if (max($w, $h) > $maxW) {
                $nw = (int) round($w * $maxW / max($w, $h));
                $nh = (int) round($h * $maxW / max($w, $h));
                $resized = imagecreatetruecolor($nw, $nh);
                // Preserve transparency through the resample for PNGs.
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                imagecopyresampled($resized, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
                imagedestroy($im);
                $im = $resized;
            }

            $toJpeg = ! $isPngBytes
                || (! $this->option('no-convert') && ! $this->hasTransparency($im));
            $tmp = $abs.'.opt';
            if ($toJpeg) {
                // JPEG has no alpha channel; flatten onto white.
                $flat = imagecreatetruecolor(imagesx($im), imagesy($im));
                imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
                imagecopy($flat, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));
                imagejpeg($flat, $tmp, self::JPEG_QUALITY);
                imagedestroy($flat);
            } else {
                imagepng($im, $tmp, 9);
            }
            imagedestroy($im);

            $newSize = filesize($tmp);
            if ($newSize >= $size) { // no win — keep the original
                unlink($tmp);

                continue;
            }

            $newRel = $toJpeg && $ext === 'png'
                ? substr($rel, 0, -4).'.jpg'
                : $rel;

            $this->line(sprintf(
                '%s%s  %s -> %s',
                $rel,
                $newRel !== $rel ? "  =>  {$newRel}" : '',
                $this->kb($size),
                $this->kb($newSize),
            ));

            $totalBefore += $size;
            $totalAfter += $newSize;
            $count++;

            if ($dry) {
                unlink($tmp);

                continue;
            }

            rename($tmp, $disk->path($newRel));
            if ($newRel !== $rel) {
                unlink($abs);
                $this->replaceDbReferences($rel, $newRel);
            }
        }

        $this->info(sprintf(
            '%s%d file(s), %s -> %s (saved %s)',
            $dry ? '[dry-run] ' : '',
            $count,
            $this->kb($totalBefore),
            $this->kb($totalAfter),
            $this->kb($totalBefore - $totalAfter),
        ));

        return self::SUCCESS;
    }

    /** Sample the alpha channel on a grid — a full per-pixel scan is too slow. */
    private function hasTransparency(\GdImage $im): bool
    {
        $w = imagesx($im);
        $h = imagesy($im);
        $stepX = max(1, intdiv($w, 200));
        $stepY = max(1, intdiv($h, 200));

        for ($y = 0; $y < $h; $y += $stepY) {
            for ($x = 0; $x < $w; $x += $stepX) {
                if (((imagecolorat($im, $x, $y) >> 24) & 0x7F) > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * A converted file changed name (x.png -> x.jpg): update every stored
     * reference. Paths contain unique ULIDs/hashes, so a plain REPLACE across
     * all string/json columns is safe; the escaped variant covers
     * json_encode's "products\/x.png" form inside JSON columns.
     */
    private function replaceDbReferences(string $old, string $new): void
    {
        $columns = DB::select(
            "SELECT TABLE_NAME t, COLUMN_NAME c FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND DATA_TYPE IN ('varchar', 'text', 'mediumtext', 'longtext', 'json')
               AND TABLE_NAME NOT IN ('migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs')"
        );

        foreach ($columns as $col) {
            foreach ([[$old, $new], [str_replace('/', '\\/', $old), str_replace('/', '\\/', $new)]] as [$from, $to]) {
                DB::update(
                    "UPDATE `{$col->t}` SET `{$col->c}` = REPLACE(`{$col->c}`, ?, ?) WHERE `{$col->c}` LIKE ?",
                    [$from, $to, '%'.$from.'%'],
                );
            }
        }
    }

    private function kb(int $bytes): string
    {
        return $bytes >= 1048576
            ? round($bytes / 1048576, 1).'MB'
            : round($bytes / 1024).'K';
    }
}
