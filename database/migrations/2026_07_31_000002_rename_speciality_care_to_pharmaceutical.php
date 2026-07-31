<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * "Speciality Care" is called "Pharmaceutical" now — the product category and
 * the home banner that carries the same title. Content is per-environment, so
 * it ships as a migration rather than a CMS edit repeated on every box, the
 * way the Technical Racing rename did.
 *
 * The slug moves to `pharmaceutical` as well. Category slugs are internal: the
 * products page is a single route with client-side tabs, and the only routable
 * query is ?product={product-slug}, so no redirect is needed. The 28 products
 * keep their category — the row is renamed in place, not replaced.
 */
return new class extends Migration
{
    private const OLD = ['Speciality Care', 'Specialty Care'];

    public function up(): void
    {
        DB::table('product_categories')->where('slug', 'speciality-care')->update([
            'slug' => 'pharmaceutical',
            'name_id' => 'Pharmaceutical',
            'name_en' => 'Pharmaceutical',
        ]);

        DB::table('product_banners')
            ->where(fn ($q) => $q->whereIn('title_id', self::OLD)->orWhereIn('title_en', self::OLD))
            ->update([
                'title_id' => 'Pharmaceutical',
                'title_en' => 'Pharmaceutical',
            ]);
    }

    public function down(): void
    {
        DB::table('product_categories')->where('slug', 'pharmaceutical')->update([
            'slug' => 'speciality-care',
            'name_id' => 'Speciality Care',
            'name_en' => 'Speciality Care',
        ]);

        DB::table('product_banners')
            ->where(fn ($q) => $q->where('title_id', 'Pharmaceutical')->orWhere('title_en', 'Pharmaceutical'))
            ->update([
                'title_id' => 'Speciality Care',
                'title_en' => 'Speciality Care',
            ]);
    }
};
