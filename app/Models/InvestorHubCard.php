<?php

namespace App\Models;

use App\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;

/**
 * A card on the Investor hub grid. `key` is fixed (it selects which Investor
 * section the card opens) — see the create migration for the seeded set.
 */
class InvestorHubCard extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];

    protected $casts = [
        'is_visible' => 'boolean',
    ];
}
