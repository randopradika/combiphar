<?php

namespace App\Models;

use App\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];

    protected $casts = [
        'under_development' => 'boolean',
        // Bilingual rich-text notes in the recruitment-fraud band (Figma 987:51).
        'fraud_items' => 'array',
    ];
}
