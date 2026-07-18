<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineShop extends Model
{
    protected $guarded = [];

    /**
     * Shops a product is shown as available at when it has no explicit
     * shop_ids selection (null). Matched by name so it survives differing
     * IDs across environments; MySQL's default _ci collation makes the
     * whereIn match case-insensitive.
     */
    public const DEFAULT_NAMES = ['Tokopedia', 'Shopee', 'Blibli'];

    /** IDs of the default shops present in this environment. */
    public static function defaultIds(): array
    {
        return static::whereIn('name', self::DEFAULT_NAMES)->pluck('id')->all();
    }
}