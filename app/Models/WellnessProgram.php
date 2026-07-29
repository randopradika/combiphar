<?php

namespace App\Models;

use App\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;

/**
 * One "Employee Wellness Program" circle on the Karir tab (Figma 987:51):
 * an icon, a title and a short description. Ordered by `sort`.
 */
class WellnessProgram extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];
}
