<?php

namespace App\Models;

use App\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CsrProgram extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];

    protected $casts = [
        'gallery' => 'array',
        'is_published' => 'boolean',
    ];

    /** Hanya program yang sudah terbit -- dipakai di setiap query publik. */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    protected static function booted(): void
    {
        // A card links to its /csr/{slug} detail page only when it has a slug.
        // Auto-derive one from the title when the admin left both the slug and
        // the external link blank, so the "Selengkapnya" button is never dead
        // (a program with an external link keeps that link — no slug needed).
        static::saving(function (self $program): void {
            $title = $program->title_id ?: $program->title_en;
            if (blank($program->slug) && blank($program->link) && filled($title)) {
                $program->slug = Str::slug($title);
            }
        });
    }

    /** Parent program (null = top-level card). */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(CsrProgram::class, 'parent_id');
    }

    /**
     * Sub-topics shown on the detail page.
     *
     * ⚠️ Constrained to published rows **on the relation itself**, not at each
     * call site. Every consumer of this relation is public rendering — five
     * places in PageController (sports/event blocks, the board check, governance
     * topics and slides) — while the CMS queries `CsrProgram` directly and is
     * unaffected. Filtering here is what makes it impossible to add a sixth
     * render site later that quietly leaks a draft.
     */
    public function children(): HasMany
    {
        return $this->hasMany(CsrProgram::class, 'parent_id')
            ->where('is_published', true)
            ->orderBy('sort');
    }
}