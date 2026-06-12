<?php

namespace Database\Factories;

use App\Models\PlatformAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Video>
 */
class VideoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platformVideoId = fake()->regexify('[A-Za-z0-9_-]{11}');

        return [
            'platform_account_id' => PlatformAccount::factory(),
            'platform_video_id' => $platformVideoId,
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'thumbnail_url' => "https://i.ytimg.com/vi/{$platformVideoId}/hqdefault.jpg",
            'embed_url' => "https://www.youtube.com/embed/{$platformVideoId}",
            'published_at' => fake()->dateTimeBetween('-2 years'),
            'duration_seconds' => fake()->numberBetween(30, 3600),
        ];
    }
}
