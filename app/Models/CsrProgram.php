<?php

namespace App\Models;

use App\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CsrProgram extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];

    protected $casts = [
        'gallery' => 'array',
    ];

    /** Parent program (null = top-level card). */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(CsrProgram::class, 'parent_id');
    }

    /** Sub-topics shown on the detail page. */
    public function children(): HasMany
    {
        return $this->hasMany(CsrProgram::class, 'parent_id')->orderBy('sort');
    }
}