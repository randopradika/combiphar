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
        // Shows/hides this page's item in the nav, mobile menu and footer.
        'show_in_menu' => 'boolean',
        // Bilingual rich-text notes in the recruitment-fraud band (Figma 987:51).
        'fraud_items' => 'array',
        // Recruitment-scam pop-up on the Karir tab (Figma 987:258).
        'scam_popup_enabled' => 'boolean',
        'scam_items' => 'array',
    ];
}
