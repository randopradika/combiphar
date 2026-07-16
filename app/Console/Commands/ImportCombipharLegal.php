<?php

namespace App\Console\Commands;

use App\Models\LegalPage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportCombipharLegal extends Command
{
    protected $signature = 'legal:import-combiphar';

    protected $description = 'Import Terms of Use + Privacy Policy content (ID + EN) from combiphar.com into the CMS.';

    /** our key => combiphar.com page id. */
    private const MAP = ['terms' => 4, 'privacy' => 3];

    public function handle(): int
    {
        foreach (self::MAP as $key => $id) {
            $row = [];
            foreach (['id', 'en'] as $loc) {
                $page = Http::acceptJson()->timeout(30)
                    ->get("https://www.combiphar.com/back/api/v1/pages/{$id}", ['locale' => $loc])
                    ->json('data.pages.data');

                if (! $page) {
                    $this->warn("No data for {$key} ({$loc}).");

                    continue;
                }

                $row["title_{$loc}"] = $page['title'] ?? null;
                $row["body_{$loc}"] = $page['body_content'] ?? null;
            }

            if ($row === []) {
                continue;
            }

            LegalPage::updateOrCreate(['key' => $key], $row);
            $this->info("Imported {$key}.");
        }

        return self::SUCCESS;
    }
}
