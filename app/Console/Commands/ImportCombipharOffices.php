<?php

namespace App\Console\Commands;

use App\Models\Office;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportCombipharOffices extends Command
{
    protected $signature = 'offices:import-combiphar';

    protected $description = 'Import branch offices (Lokasi Kami) from combiphar.com into the CMS.';

    /** Distribution categories on the combiphar distributions API. */
    private const CATEGORIES = ['branch', 'distributor', 'factory'];

    /** Region slugs used by the combiphar distributions API. */
    private const LOCATIONS = ['jabodetabek', 'jawa', 'sumatra', 'kalimantan', 'sulawesi', 'bali', 'papua'];

    public function handle(): int
    {
        $sort = 0;
        $count = 0;

        foreach (self::CATEGORIES as $cat) {
            foreach (self::LOCATIONS as $loc) {
                $response = Http::acceptJson()->timeout(30)->get('https://www.combiphar.com/back/api/v1/distributions', [
                    'locale' => 'id',
                    'category' => $cat,
                    'location' => $loc,
                ]);

                $items = data_get($response->json(), 'data.distributions', []);

                foreach ($items as $d) {
                    $name = trim($d['title'] ?? '');
                    if ($name === '') {
                        continue;
                    }
                    $address = preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags(str_replace('&nbsp;', ' ', $d['address'] ?? '')), ENT_QUOTES)));

                    Office::updateOrCreate(
                        [
                            'name' => $name,
                            'category' => $d['category'] ?? ucfirst($cat),
                            'city' => $d['location'] ?? ucfirst($loc),
                        ],
                        [
                            'description_id' => $address,
                            'description_en' => $address,
                            'sort' => $sort++,
                        ]
                    );
                    $count++;
                }
            }
        }

        $this->info("Imported/updated {$count} offices.");

        return self::SUCCESS;
    }
}
