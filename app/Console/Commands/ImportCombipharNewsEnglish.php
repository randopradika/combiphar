<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Fill the English columns of articles previously imported by `news:import-combiphar`.
 *
 * That importer read combiphar.com with locale=id and copied the Indonesian text into
 * BOTH language columns. The remote API exposes the same article under a stable numeric
 * `id` in every locale, but with a locale-specific `slug` — so the mapping here is:
 *
 *   remote id  →  ID-locale slug (what `articles.slug` holds)  →  local Article  →  write *_en
 *
 * Only `title_en` / `excerpt_en` / `body_en` / `slug_en` are written (plus `cover_image`
 * when it is still null). Indonesian columns, category, dates and the Indonesian `slug`
 * are never touched, so the command is safe to re-run on any environment where editors
 * may have edited ID copy.
 */
class ImportCombipharNewsEnglish extends Command
{
    protected $signature = 'news:import-combiphar-en
                            {--dry-run : Resolve and report the mapping without writing anything}';

    protected $description = 'Fill English title/excerpt/body of combiphar.com-imported articles, mapped by the remote article id.';

    private const API = 'https://www.combiphar.com/back/api/v1/articles';

    /** Remote article category ids imported by news:import-combiphar (Info Kesehatan, Siaran Pers). */
    private const CATEGORIES = [1, 2];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $totals = ['updated' => 0, 'unchanged' => 0, 'missing_local' => 0, 'missing_en' => 0, 'untranslated' => 0, 'date_mismatch' => 0, 'slug_collision' => 0];

        foreach (self::CATEGORIES as $remoteCategory) {
            $idRows = $this->fetchAll('id', $remoteCategory);
            $enRows = $this->fetchAll('en', $remoteCategory);

            $this->line(sprintf('Category %d: %d ID rows, %d EN rows on combiphar.com', $remoteCategory, count($idRows), count($enRows)));

            foreach ($idRows as $remoteId => $id) {
                $en = $enRows[$remoteId] ?? null;
                if (! $en) {
                    $totals['missing_en']++;
                    $this->warn("  [{$remoteId}] no EN version on source: {$id['slug']}");

                    continue;
                }

                $article = Article::where('slug', $id['slug'])->first();
                if (! $article) {
                    $totals['missing_local']++;
                    $this->warn("  [{$remoteId}] not in local DB (run news:import-combiphar first): {$id['slug']}");

                    continue;
                }

                if (($id['display_date'] ?? null) !== ($en['display_date'] ?? null)) {
                    $totals['date_mismatch']++;
                    $this->warn("  [{$remoteId}] display_date differs between locales ({$id['display_date']} vs {$en['display_date']}) — still mapped by id: {$id['slug']}");
                }

                $title = trim($en['title'] ?? '');
                $excerpt = trim(strip_tags(str_replace('&nbsp;', ' ', $en['og_description'] ?? '')));
                $body = trim($en['body'] ?? '');

                if ($title !== '' && $title === trim($id['title'] ?? '')) {
                    $totals['untranslated']++;
                    $this->comment("  [{$remoteId}] EN title identical to ID on source (not translated there): {$id['slug']}");
                }

                // Only overwrite with real content — an empty source field would leave the EN page blank,
                // whereas the existing Indonesian copy at least renders something.
                $attrs = array_filter([
                    'title_en' => $title,
                    'excerpt_en' => $excerpt,
                    'body_en' => $body,
                ], fn ($v) => $v !== '');

                // English slug → /en/news/{slug_en}, mirroring combiphar.com's per-locale URLs.
                // `slug_en` is unique, and an English slug that equals ANOTHER article's Indonesian
                // slug would make the lookup ambiguous — skip (and say so) rather than throw mid-run.
                $slugEn = trim($en['slug'] ?? '');
                if ($slugEn !== '' && $slugEn !== $article->slug_en) {
                    $taken = Article::where('id', '!=', $article->id)
                        ->where(fn ($q) => $q->where('slug', $slugEn)->orWhere('slug_en', $slugEn))
                        ->exists();
                    if ($taken) {
                        $totals['slug_collision']++;
                        $this->warn("  [{$remoteId}] EN slug '{$slugEn}' already used by another article — slug_en left as is: {$id['slug']}");
                    } else {
                        $attrs['slug_en'] = $slugEn;
                    }
                }

                if (! $article->cover_image && ! empty($en['image'])) {
                    $attrs['cover_image'] = $dry ? $en['image'] : $this->downloadImage($en['image']);
                }

                $article->fill($attrs);
                $changed = array_keys($article->getDirty());

                if ($changed === []) {
                    $totals['unchanged']++;

                    continue;
                }

                $totals['updated']++;
                $this->line(sprintf(
                    '  [%d] %s  →  %s%s',
                    $remoteId,
                    $id['slug'],
                    $en['slug'] ?? '(no en slug)',
                    $dry ? '  [dry-run: '.implode(',', $changed).']' : ''
                ));

                if (! $dry) {
                    $article->save();
                }
            }
        }

        $this->newLine();
        $this->info(($dry ? '[dry-run] ' : '').sprintf(
            'updated=%d unchanged=%d missing_local=%d missing_en=%d untranslated_on_source=%d date_mismatch=%d slug_collision=%d',
            $totals['updated'],
            $totals['unchanged'],
            $totals['missing_local'],
            $totals['missing_en'],
            $totals['untranslated'],
            $totals['date_mismatch'],
            $totals['slug_collision'],
        ));

        return self::SUCCESS;
    }

    /** All articles of one remote category in one locale, keyed by the remote article id. */
    private function fetchAll(string $locale, int $remoteCategory): array
    {
        $rows = [];
        $page = 1;

        do {
            $response = Http::acceptJson()->timeout(30)->retry(2, 1000)->get(self::API, [
                'locale' => $locale,
                'articleCategoryId' => $remoteCategory,
                'page' => $page,
                'pageSize' => 25,
            ]);

            $paginator = data_get($response->json(), 'data.articles', []);
            $lastPage = (int) ($paginator['last_page'] ?? 1);

            foreach ($paginator['data'] ?? [] as $a) {
                if (! isset($a['id'])) {
                    continue;
                }
                $rows[(int) $a['id']] = $a;
            }

            $page++;
        } while ($page <= $lastPage);

        return $rows;
    }

    /** Same storage convention as ImportCombipharNews: storage/app/public/articles/<basename>. */
    private function downloadImage(string $url): string
    {
        $filename = basename((string) parse_url($url, PHP_URL_PATH));
        if ($filename === '') {
            return $url;
        }
        $path = 'articles/'.$filename;

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

        return $url;
    }
}
