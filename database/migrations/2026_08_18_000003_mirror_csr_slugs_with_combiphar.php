<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * CSR programme slugs mirror combiphar.com (its community slugs, identical in
 * both locales, read from /back/api/v1/pages/find?slug=csr-community on
 * 2026-08-18): environmental → environmental-care-action, social →
 * social-care-action. Running/golf/tennis/basketball/education/empowerment
 * already matched. Rows are per environment, so this is a data migration keyed
 * on the current slug — guarded never to clobber a row that already holds the
 * target slug. The old slugs keep working via a 301 in PageController::csrShow().
 *
 * Also normalises any slug that is not slug-shaped (dev has an `Empowerment`
 * with a capital E): MySQL's case-insensitive collation lets `Empowerment` and
 * `empowerment` coexist AND both match a lookup for either, so the public URL
 * is ambiguous until the row is lower-cased. Skipped (and logged) when another
 * row already owns the normalised slug — that is a content decision, not one a
 * migration should make.
 */
return new class extends Migration
{
    private const RENAMES = [
        'environmental' => 'environmental-care-action',
        'social' => 'social-care-action',
    ];

    public function up(): void
    {
        foreach (self::RENAMES as $old => $new) {
            $this->rename($old, $new);
            DB::table('nav_items')->where('suffix', '/'.$old)->update(['suffix' => '/'.$new]);
        }

        // Non-slug-shaped slugs (case, spaces, accents). Compare in PHP, not SQL —
        // the collation would call `Empowerment` equal to `empowerment`.
        foreach (DB::table('csr_programs')->whereNotNull('slug')->get(['id', 'slug']) as $row) {
            $normalised = Str::slug($row->slug);
            if ($normalised === '' || $normalised === $row->slug) {
                continue;
            }
            $taken = DB::table('csr_programs')->where('id', '!=', $row->id)->where('slug', $normalised)->exists();
            if ($taken) {
                Log::warning("[2026_08_18_000003] csr_programs#{$row->id} slug '{$row->slug}' left as is: '{$normalised}' belongs to another row");
                echo "csr slug NOT normalised (collision): #{$row->id} '{$row->slug}' → '{$normalised}'\n";

                continue;
            }
            DB::table('csr_programs')->where('id', $row->id)->update(['slug' => $normalised]);
            echo "csr slug normalised: #{$row->id} '{$row->slug}' → '{$normalised}'\n";
        }
    }

    public function down(): void
    {
        foreach (self::RENAMES as $old => $new) {
            $this->rename($new, $old);
            DB::table('nav_items')->where('suffix', '/'.$new)->update(['suffix' => '/'.$old]);
        }
        // Case normalisation is not reversed — the original casing is not recorded.
    }

    /** Rename a programme slug unless the target already exists (then log and skip). */
    private function rename(string $from, string $to): void
    {
        if (! DB::table('csr_programs')->where('slug', $from)->exists()) {
            return;
        }
        if (DB::table('csr_programs')->where('slug', $to)->exists()) {
            Log::warning("[2026_08_18_000003] csr_programs slug '{$from}' not renamed: '{$to}' already exists");
            echo "csr slug NOT renamed (target exists): '{$from}' → '{$to}'\n";

            return;
        }
        DB::table('csr_programs')->where('slug', $from)->update(['slug' => $to]);
        echo "csr slug renamed: '{$from}' → '{$to}'\n";
    }
};
