<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Product detail popup gains an official website link + Instagram / Facebook
     * social links (each rendered only when filled). The columns are CMS-editable
     * (ProductResource); this migration also backfills the known brand links by
     * matching product names, so every variant of a brand (e.g. all 15 OBH Combi
     * SKUs, all Simba / Madurasa / Insto / Air Mancur items) gets its brand's
     * links at once. Brands whose products are not imported yet (Fortiboost,
     * Maltofer, Uricran, Jointfit) match zero rows for now — their links apply
     * automatically once those products exist, or are set via the CMS.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('website_url')->nullable()->after('shop_ids');
            $table->string('instagram_url')->nullable()->after('website_url');
            $table->string('facebook_url')->nullable()->after('instagram_url');
        });

        // brand name keyword => [website, instagram, facebook]  (null = leave empty)
        $brands = [
            'Fortiboost' => ['https://fortiboost.co.id/', 'https://www.instagram.com/fortiboost.id', 'https://www.facebook.com/p/Fortiboostid-100075922629727/'],
            'Maltofer' => ['https://maltofer.combiphar.com/', 'https://www.instagram.com/maltoferid/', 'https://www.facebook.com/MaltoferIndonesia/?locale=id_ID'],
            'Uricran' => ['https://www.uricran.co.id/', 'https://www.instagram.com/uricran.id/', 'https://www.facebook.com/priveuricran/'],
            'Insto' => ['https://insto.co.id/', 'https://www.instagram.com/INSTO.ID/', 'https://www.facebook.com/bukamatabukainsto/?locale=id_ID'],
            'OBH' => ['https://obhcombi.co.id/', 'https://www.instagram.com/obh.combi/', 'https://www.facebook.com/Combination.ID/?locale=id_ID'],
            'Simba' => ['https://simba.co.id/', 'https://www.instagram.com/simba.cereal/?hl=id', 'https://www.facebook.com/Simba.cereal/'],
            'Madurasa' => ['https://madurasa.co.id/', 'https://www.instagram.com/madurasa_official/', 'https://www.facebook.com/MadurasaOfficial/'],
            'Air Mancur' => ['https://www.airmancur.co.id/', 'https://www.instagram.com/airmancur_/', 'https://www.facebook.com/airmancurofficial/photos/'],
            'Jointfit' => [null, 'https://www.instagram.com/jointfitid/', 'https://www.facebook.com/JointfitID/'],
        ];

        foreach ($brands as $keyword => [$website, $instagram, $facebook]) {
            // Only set the fields that have a value (e.g. Jointfit has no website).
            $values = array_filter([
                'website_url' => $website,
                'instagram_url' => $instagram,
                'facebook_url' => $facebook,
            ], fn ($v) => $v !== null);

            DB::table('products')
                ->where(fn ($q) => $q
                    ->where('name_id', 'like', '%'.$keyword.'%')
                    ->orWhere('name_en', 'like', '%'.$keyword.'%'))
                ->update($values);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['website_url', 'instagram_url', 'facebook_url']);
        });
    }
};
