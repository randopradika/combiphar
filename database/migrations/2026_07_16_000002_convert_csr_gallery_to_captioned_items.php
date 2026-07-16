<?php

use App\Models\CsrProgram;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Convert CSR (non-sports) gallery from a plain array of image paths to an
     * array of {image, caption_id, caption_en} so each photo can have its own
     * bilingual caption. Sports team galleries keep the plain-string shape.
     */
    public function up(): void
    {
        foreach (CsrProgram::where('category', '!=', 'sports')->get() as $p) {
            $g = $p->gallery;
            if (! is_array($g) || $g === []) {
                continue;
            }
            // Already converted (items are associative arrays)?
            if (is_array($g[0] ?? null)) {
                continue;
            }
            $p->gallery = array_map(fn ($path) => [
                'image' => $path,
                'caption_id' => null,
                'caption_en' => null,
            ], $g);
            $p->save();
        }
    }

    public function down(): void
    {
        foreach (CsrProgram::where('category', '!=', 'sports')->get() as $p) {
            $g = $p->gallery;
            if (! is_array($g) || $g === []) {
                continue;
            }
            if (! is_array($g[0] ?? null)) {
                continue;
            }
            $p->gallery = array_values(array_filter(array_map(fn ($item) => $item['image'] ?? null, $g)));
            $p->save();
        }
    }
};
