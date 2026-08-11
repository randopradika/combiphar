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
}