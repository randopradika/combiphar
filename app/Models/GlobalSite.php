<?php

namespace App\Models;

use App\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;

class GlobalSite extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];
}