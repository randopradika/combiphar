<?php

namespace App\Models;

use App\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];

    protected $casts = [
        'show_on_page' => 'boolean',
    ];

    /** Members the CMS says to show on the public page. */
    public function scopeVisible($query)
    {
        return $query->where('show_on_page', true);
    }
}