<?php

namespace App\Console\Commands;

use App\Models\Episode;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class ScrapeOnePieceEpisodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:onepiece {url?} {--json=} {--embeds=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape One Piece episode watch URLs from the given page and save them.';

    public function handle(): int
    {
        $embedsPath = $this->option('embeds');
        if ($embedsPath) {
            $this->info('Importing episode embed URLs from JSON: ' . $embedsPath);
            if (!is_file($embedsPath)) {
                $this->error('Embeds JSON file not found: ' . $embedsPath);
                return self::FAILURE;
            }
            $contents = @file_get_contents($embedsPath);
            if ($contents === false) {
                $this->error('Failed to read embeds JSON file.');
                return self::FAILURE;
            }
            $decoded = @json_decode($contents, true);
            if (!is_array($decoded)) {
                $this->error('Invalid embeds JSON format: expected an array of objects.');
                return self::FAILURE;
            }
            $updated = $this->persistEmbeds($decoded);
            $this->info('Updated embed URLs for ' . $updated . ' episodes.');
            return self::SUCCESS;
        }

        $jsonPath = $this->option('json');
        if ($jsonPath) {
            $this->info('Importing episode URLs from JSON: ' . $jsonPath);
            if (!is_file($jsonPath)) {
                $this->error('JSON file not found: ' . $jsonPath);
                return self::FAILURE;
            }
            $contents = @file_get_contents($jsonPath);
            if ($contents === false) {
                $this->error('Failed to read JSON file.');
                return self::FAILURE;
            }
            $decoded = @json_decode($contents, true);
            if (!is_array($decoded)) {
                $this->error('Invalid JSON format: expected an array of URLs.');
                return self::FAILURE;
            }
            $urlList = array_values(array_unique(array_map('strval', $decoded)));
            sort($urlList);

            if (empty($urlList)) {
                $this->warn('No URLs found in JSON.');
                return self::SUCCESS;
            }

            $this->info('Found ' . count($urlList) . ' episode URLs in JSON. Saving...');
            return $this->persistUrls($urlList);
        }

        $url = (string) ($this->argument('url') ?? 'https://hianime.to/watch/one-piece-100?ep=160237');
        $this->info("Fetching: {$url}");

        $client = new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ],
            'http_errors' => false,
            'timeout' => 20,
        ]);

        $htmlPages = [];
        // Fetch the provided URL
        try {
            $response = $client->get($url);
            if ($response->getStatusCode() === 200) {
                $htmlPages[] = (string) $response->getBody();
            } else {
                $this->warn('Got status ' . $response->getStatusCode() . ' for provided URL.');
            }
        } catch (\Throwable $e) {
            $this->warn('Failed fetching provided URL: ' . $e->getMessage());
        }

        // Also fetch the series page (strip query string)
        $seriesUrl = preg_replace('/\?.*$/', '', $url) ?? $url;
        if ($seriesUrl !== $url) {
            $this->info('Also fetching series page: ' . $seriesUrl);
            try {
                $resp2 = $client->get($seriesUrl);
                if ($resp2->getStatusCode() === 200) {
                    $htmlPages[] = (string) $resp2->getBody();
                } else {
                    $this->warn('Got status ' . $resp2->getStatusCode() . ' for series page.');
                }
            } catch (\Throwable $e) {
                $this->warn('Failed fetching series page: ' . $e->getMessage());
            }
        }

        if (empty($htmlPages)) {
            $this->error('No HTML pages fetched successfully.');
            return self::FAILURE;
        }
        $base = 'https://hianime.to';

        // Find absolute and relative episode links
        $matches = [];
        $urls = [];
        foreach ($htmlPages as $html) {
            // Absolute links
            if (preg_match_all('#https?://hianime\.to/watch/one-piece-100\?ep=\d+#i', $html, $matches)) {
                foreach ($matches[0] as $m) {
                    $urls[$m] = true;
                }
            }

            // Relative links
            if (preg_match_all('#href="(/watch/one-piece-100\?ep=\d+)"#i', $html, $matches)) {
                foreach ($matches[1] as $m) {
                    $full = rtrim($base, '/') . $m;
                    $urls[$full] = true;
                }
            }

            // Some pages may embed episodes in data attributes
            if (preg_match_all('#/watch/one-piece-100\?ep=\d+#i', $html, $matches)) {
                foreach ($matches[0] as $m) {
                    $full = str_starts_with($m, 'http') ? $m : rtrim($base, '/') . $m;
                    $urls[$full] = true;
                }
            }
        }

        $urlList = array_keys($urls);
        sort($urlList);

        if (empty($urlList)) {
            $this->warn('No episode URLs found. The page may be dynamic; consider a different source or approach.');
            return self::SUCCESS;
        }

        $this->info('Found ' . count($urlList) . ' episode URLs. Saving...');
        return $this->persistUrls($urlList);
    }

    private function persistUrls(array $urlList): int
    {
        $maxNumber = (int) (Episode::max('number') ?? 0);
        $created = 0;
        $skipped = 0;

        foreach ($urlList as $i => $episodeUrl) {
            $exists = Episode::where('video_url', $episodeUrl)->first();
            if ($exists) {
                $skipped++;
                continue;
            }

            $num = $maxNumber + $i + 1;
            Episode::create([
                'number' => $num,
                'title' => 'One Piece — Episode ' . $num,
                'synopsis' => null,
                'video_url' => $episodeUrl,
                'aired_at' => null,
            ]);

            $created++;
        }

        $this->info("Created {$created}, skipped {$skipped} already existing.");
        $this->info('Done. Note: these are watch-page URLs, not direct video streams.');
        return self::SUCCESS;
    }

    private function persistEmbeds(array $rows): int
    {
        $updated = 0;
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $watch = $row['watch_url'] ?? $row['url'] ?? null;
            if (!$watch || !is_string($watch)) {
                continue;
            }
            $embed = null;
            if (isset($row['embed_url']) && is_string($row['embed_url'])) {
                $embed = $row['embed_url'];
            } elseif (!empty($row['iframe_urls']) && is_array($row['iframe_urls'])) {
                $embed = (string) ($row['iframe_urls'][0] ?? null);
            } elseif (!empty($row['video_sources']) && is_array($row['video_sources'])) {
                $embed = (string) ($row['video_sources'][0] ?? null);
            }
            if (!$embed) {
                continue;
            }

            $episode = Episode::where('video_url', $watch)->first();
            if (!$episode) {
                // Try to match by ep id parameter (normalize URL)
                $epId = null;
                if (preg_match('/[?&]ep=(\d+)/', $watch, $m)) {
                    $epId = $m[1];
                }
                if ($epId) {
                    $episode = Episode::query()
                        ->where('video_url', 'like', '%?ep='.$epId.'%')
                        ->orWhere('video_url', 'like', '%&ep='.$epId.'%')
                        ->first();
                }
                // Try to match animeflv watch URL patterns by episode number
                if (!$episode) {
                    $num = null;
                    // e.g., https://www3.animeflv.net/ver/one-piece-tv-1
                    if (preg_match('#/ver/[^/]*-tv-(\d+)#', $watch, $m)) {
                        $num = (int) $m[1];
                    } elseif (preg_match('#/ver/.*-(\d+)$#', $watch, $m)) {
                        $num = (int) $m[1];
                    }
                    if ($num) {
                        $episode = Episode::where('number', $num)->first();
                    }
                }
            }
            if ($episode) {
                $episode->embed_url = $embed;
                $episode->save();
                $updated++;
            }
        }
        return $updated;
    }
}