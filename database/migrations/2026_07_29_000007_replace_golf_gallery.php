<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Replace the Golf photo gallery with the supplied "golf pic" set (30 images).
 *
 * The images ship in the repo under storage/app/public/sports/golf/ (tracked
 * past the storage gitignore, like the award images), so a deploy's
 * `git reset --hard` delivers the files and this migration repoints the rows —
 * that is what makes the replacement happen on the dev server too. The photos
 * it replaces there are CMS uploads at the storage root; they stay on disk but
 * are no longer referenced.
 *
 * Golf is located by walking from the top-level sports row to its children
 * rather than by id, because content ids differ per environment.
 */
return new class extends Migration
{
    /** @var list<string> */
    private const GALLERY = [
        'sports/golf/6o5a9889.webp',
        'sports/golf/aa5t1811.webp',
        'sports/golf/aa5t1842.webp',
        'sports/golf/aa5t2100.webp',
        'sports/golf/aa5t2107.webp',
        'sports/golf/aa5t2142.webp',
        'sports/golf/aa5t2170.webp',
        'sports/golf/c46i2493.webp',
        'sports/golf/img-0671.webp',
        'sports/golf/img-0697.webp',
        'sports/golf/img-0734.webp',
        'sports/golf/img-0757.webp',
        'sports/golf/img-3378.webp',
        'sports/golf/img-3389.webp',
        'sports/golf/img-3436.webp',
        'sports/golf/img-3780.webp',
        'sports/golf/img-3937.webp',
        'sports/golf/img-4017.webp',
        'sports/golf/img-4136.webp',
        'sports/golf/img-4412.webp',
        'sports/golf/img-4581.webp',
        'sports/golf/img-4669.webp',
        'sports/golf/img-4706.webp',
        'sports/golf/img-4754.webp',
        'sports/golf/tipa2024-east-player-r2-0030.webp',
        'sports/golf/tipa2024-east-player-r2-0172.webp',
        'sports/golf/tipa2024-final-round-players-0127.webp',
        'sports/golf/tipa2024-final-round-players-0158.webp',
        'sports/golf/combiphar-4143.webp',
        'sports/golf/combiphar-5012.webp',
    ];

    public function up(): void
    {
        $golf = DB::table('csr_programs')
            ->where('category', 'sports')
            ->whereNull('parent_id')
            ->where(function ($q) {
                $q->where('slug', 'golf')
                    ->orWhere('title_id', 'Golf')
                    ->orWhere('title_en', 'Golf');
            })
            ->first();

        if (! $golf) {
            return;
        }

        $gallery = json_encode(self::GALLERY);

        // Sports galleries are plain string arrays (captioned objects are for
        // CSR programs only — see 2026_07_16_000002).
        $teams = DB::table('csr_programs')->where('parent_id', $golf->id)->count();

        if ($teams > 0) {
            DB::table('csr_programs')->where('parent_id', $golf->id)->update(['gallery' => $gallery]);

            return;
        }

        // No team rows on this environment — keep the set on the sport itself so
        // the images are not orphaned once a team is added.
        DB::table('csr_programs')->where('id', $golf->id)->update(['gallery' => $gallery]);
    }

    /**
     * Deliberately a no-op: the replaced galleries pointed at per-environment
     * CMS uploads that this migration cannot reconstruct.
     */
    public function down(): void {}
};
