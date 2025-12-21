<?php

namespace Database\Seeders;

use App\Models\Episode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EpisodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $episodes = [
            [
                'number' => 1,
                'title' => 'Romance Dawn: A New Adventure',
                'synopsis' => 'Luffy sets sail to become Pirate King.',
                'video_url' => 'https://example.com/videos/one-piece-001.mp4',
                'aired_at' => '1999-10-20',
            ],
            [
                'number' => 2,
                'title' => 'Entering the Grand Line',
                'synopsis' => 'Straw Hats head to the Grand Line.',
                'video_url' => 'https://example.com/videos/one-piece-002.mp4',
                'aired_at' => '1999-10-27',
            ],
            [
                'number' => 3,
                'title' => 'The First Battle',
                'synopsis' => 'Luffy faces a formidable foe.',
                'video_url' => 'https://example.com/videos/one-piece-003.mp4',
                'aired_at' => '1999-11-03',
            ],
            [
                'number' => 4,
                'title' => 'A Crew Assembles',
                'synopsis' => 'New comrades join the ship.',
                'video_url' => 'https://example.com/videos/one-piece-004.mp4',
                'aired_at' => '1999-11-10',
            ],
            [
                'number' => 5,
                'title' => 'Setting Our Course',
                'synopsis' => 'The journey truly begins.',
                'video_url' => 'https://example.com/videos/one-piece-005.mp4',
                'aired_at' => '1999-11-17',
            ],
        ];

        foreach ($episodes as $ep) {
            Episode::updateOrCreate(
                ['number' => $ep['number']],
                $ep
            );
        }
    }
}
