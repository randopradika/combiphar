<?php

namespace App\Models;

use App\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];
}