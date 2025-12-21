<?php

namespace App\Console\Commands;

use App\Models\Episode;
use Illuminate\Console\Command;

class PopulateOnePieceEpisodes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'episodes:populate-onepiece {--total=1554} {--start=1}';

    /**
     * The console command description.
     */
    protected $description = 'Ensure One Piece episodes 1..N exist and set video_url to animeflv watch pages.';

    public function handle(): int
    {
        $start = max(1, (int) $this->option('start'));
        $total = max($start, (int) $this->option('total'));

        $created = 0;
        $updated = 0;

        for ($num = $start; $num <= $total; $num++) {
            $url = 'https://www3.animeflv.net/ver/one-piece-tv-' . $num;

            $ep = Episode::firstOrNew(['number' => $num]);
            $ep->title = $ep->title ?: ('One Piece — Episode ' . $num);
            $ep->video_url = $url; // prefer animeflv watch page in iframe
            if (!$ep->exists) {
                $ep->save();
                $created++;
            } else {
                // Only update if URL changed or empty
                if ($ep->isDirty('video_url')) {
                    $ep->save();
                    $updated++;
                }
            }
        }

        $this->info("Episodes populated. Created: {$created}, Updated: {$updated}.");
        return self::SUCCESS;
    }
}