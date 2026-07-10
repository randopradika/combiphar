<?php

namespace App\Models;

use App\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];
}