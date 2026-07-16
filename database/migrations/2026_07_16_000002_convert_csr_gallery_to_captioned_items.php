<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert CSR (non-sports) gallery from a plain array of image paths to an
     * array of {image, caption_id, caption_en} so each photo can have its own
     * bilingual caption. Sports team galleries keep the plain-string shape.
     * Uses the DB facade + json en/decode directly so it does not depend on the
     * CsrProgram model or its `gallery` cast.
     */
    public function up(): void
    {
        DB::table('csr_programs')->where('category', '!=', 'sports')
            ->whereNotNull('gallery')->orderBy('id')
            ->each(function ($row) {
                $g = json_decode($row->gallery ?? '', true);
                if (! is_array($g) || $g === [] || is_array($g[0] ?? null)) {
                    return; // empty, invalid, or already converted
                }
                $items = array_map(fn ($path) => [
                    'image' => $path,
                    'caption_id' => null,
                    'caption_en' => null,
                ], $g);
                DB::table('csr_programs')->where('id', $row->id)->update(['gallery' => json_encode($items)]);
            });
    }

    public function down(): void
    {
        DB::table('csr_programs')->where('category', '!=', 'sports')
            ->whereNotNull('gallery')->orderBy('id')
            ->each(function ($row) {
                $g = json_decode($row->gallery ?? '', true);
                if (! is_array($g) || $g === [] || ! is_array($g[0] ?? null)) {
                    return;
                }
                $paths = array_values(array_filter(array_map(fn ($item) => $item['image'] ?? null, $g)));
                DB::table('csr_programs')->where('id', $row->id)->update(['gallery' => json_encode($paths)]);
            });
    }
};
