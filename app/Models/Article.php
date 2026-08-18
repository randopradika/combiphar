<?php

namespace App\Models;

use App\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * The single-record form of scopePublished().
     *
     * Deliberately kept in step with the scope above: no date means draft, and a
     * future date means scheduled, not live. Used by the news detail page, which
     * loads by slug and so cannot go through the scope.
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lte(now());
    }

    /**
     * The slug an article is addressed by in one locale, mirroring combiphar.com:
     * `slug` is the Indonesian one (/id/berita/{slug}) and `slug_en` the English one
     * (/en/news/{slug_en}). An article without an English slug keeps using the
     * Indonesian one on /en — nothing is ever unreachable.
     */
    public function slugFor(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return $locale === 'en' && filled($this->slug_en) ? $this->slug_en : $this->slug;
    }

    /**
     * Resolve an article from a URL slug: the locale's own column first, then the
     * other one, so an Indonesian slug pasted under /en (or vice versa) still finds
     * the article. The caller decides whether to redirect to slugFor($locale).
     */
    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale ??= app()->getLocale();
        [$native, $other] = $locale === 'en' ? ['slug_en', 'slug'] : ['slug', 'slug_en'];

        return static::where($native, $slug)->first() ?? static::where($other, $slug)->first();
    }
}
