<?php

use App\Models\Award;
use Illuminate\Database\Migrations\Migration;

/**
 * Re-point the "Pencapaian & Penghargaan" popup at the re-edited award set
 * (90 images, 2005–2026, imported from the curated per-year folder).
 *
 * The earlier import migration (2026_07_22_000005) only upserts what is listed
 * in database/data/awards.json, so on an environment that already ran it the 95
 * superseded rows would linger next to the new ones. This migration therefore
 * deletes any `awards/…` row that is no longer in the JSON, then upserts the
 * current list. Their image files are removed from the repo in the same commit,
 * so the deploy's `git reset --hard` drops the files and this drops the rows.
 *
 * Placeholder/hero awards live under `seed/awards/…` and are deliberately left
 * alone — they feed the About page hero logo row.
 */
return new class extends Migration
{
    public function up(): void
    {
        $file = database_path('data/awards.json');

        if (! is_file($file)) {
            return;
        }

        $rows = json_decode(file_get_contents($file), true) ?: [];
        $rows = array_values(array_filter($rows, fn ($r) => ! empty($r['image'])));

        if (! $rows) {
            return;
        }

        $keep = array_column($rows, 'image');

        // Retire superseded imports. Scoped to `awards/` so the seed/hero rows
        // survive, and driven by the JSON so it stays correct on re-runs.
        Award::where('image', 'like', 'awards/%')
            ->whereNotIn('image', $keep)
            ->delete();

        foreach ($rows as $i => $row) {
            Award::updateOrCreate(
                ['image' => $row['image']],
                [
                    'title_id' => $row['title_id'] ?? null,
                    'title_en' => $row['title_en'] ?? null,
                    'year' => $row['year'] ?? null,
                    'is_hero' => false,
                    'sort' => 1000 + $i,
                ]
            );
        }
    }

    /**
     * Deliberately a no-op: the superseded rows point at image files this commit
     * deletes, so recreating them would only produce broken images.
     */
    public function down(): void {}
};
