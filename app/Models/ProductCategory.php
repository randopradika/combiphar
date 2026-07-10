<?php

namespace App\Models;

use App\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('sort');
    }
}