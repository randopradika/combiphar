<?php

namespace App\Models;

use App\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;

class JobVacancy extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];

    protected $casts = ['is_open' => 'boolean'];
}