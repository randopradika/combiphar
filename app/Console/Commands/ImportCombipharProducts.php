<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportCombipharProducts extends Command
{
    protected $signature = 'products:import-combiphar';

    protected $description = 'Import Pharma + Consumer Healthcare products from combiphar.com into the CMS (Nutritions & Herbal skipped).';

    /** Remote category id (combiphar.com) => [our slug, display name, parent slug|null]. */
    private const MAP = [
        2 => ['pharmaceutical', 'Pharmaceutical', null],                              // Pharma
        3 => ['consumer-health', 'Consumer Health', null],                            // Consumer Healthcare
        5 => ['nutrition-herbal-serealsnack', 'Sereal & Snack', 'nutrition-herbal'],  // Cereal
        6 => ['nutrition-herbal-honey', 'Honey', 'nutrition-herbal'],                 // Honey
        7 => ['nutrition-herbal-herbal', 'Herbal', 'nutrition-herbal'],               // Herbal (sub-category, created if missing)
        // 1 => Wellness — not imported (per request)
    ];

    public function handle(): int
    {
        foreach (self::MAP as $remoteId => [$slug, $name, $parentSlug]) {
            $category = ProductCategory::firstOrCreate(
                ['slug' => $slug],
                [
                    'name_id' => $name,
                    'name_en' => $name,
                    'parent_id' => $parentSlug ? ProductCategory::where('slug', $parentSlug)->value('id') : null,
                ]
            );

            $response = Http::acceptJson()->timeout(30)->get('https://www.combiphar.com/back/api/v1/products', [
                'locale' => 'id',
                'categoryId' => $remoteId,
                'page' => 1,
                'pageSize' => 300,
            ]);

            $items = data_get($response->json(), 'data.products.data', []);
            $count = 0;

            foreach ($items as $i => $p) {
                $name = trim($p['title'] ?? '');
                if ($name === '') {
                    continue;
                }
                $desc = trim(strip_tags(str_replace('&nbsp;', ' ', $p['summary'] ?? '')));

                Product::updateOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'product_category_id' => $category->id,
                        'name_id' => $name,
                        'name_en' => $name,
                        'description_id' => $desc,
                        'description_en' => $desc,
                        'image' => $this->downloadImage($p['image'] ?? null),
                        'sort' => $i,
                    ]
                );
                $count++;
            }

            $this->info("{$slug}: imported/updated {$count} products (remote category {$remoteId}).");
        }

        return self::SUCCESS;
    }

    /** Download a remote product image into storage/app/public/products; returns the stored path (or the URL on failure). */
    private function downloadImage(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $filename = basename((string) parse_url($url, PHP_URL_PATH));
        if ($filename === '') {
            return $url;
        }
        $path = 'products/'.$filename;

        if (Storage::disk('public')->exists($path)) {
            return $path;
        }

        try {
            $response = Http::timeout(30)->get($url);
            if ($response->successful()) {
                Storage::disk('public')->put($path, $response->body());

                return $path;
            }
        } catch (\Throwable $e) {
            $this->warn("Image download failed for {$url}: {$e->getMessage()}");
        }

        return $url; // fallback: hotlink (PageController::img() passes absolute URLs through)
    }
}
