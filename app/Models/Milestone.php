<?php

namespace App\Models;

use App\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];
}