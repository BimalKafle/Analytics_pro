<?php

namespace Database\Factories;

use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AnalyticsSnapshot>
 */
class AnalyticsSnapshotFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $views = fake()->numberBetween(100, 1_000_000);

        return [
            'video_id' => Video::factory(),
            'snapshot_date' => fake()->dateTimeBetween('-90 days')->format('Y-m-d'),
            'views' => $views,
            'likes' => (int) round($views * fake()->randomFloat(3, 0.01, 0.1)),
            'comments' => (int) round($views * fake()->randomFloat(3, 0.001, 0.01)),
            'shares' => (int) round($views * fake()->randomFloat(3, 0.001, 0.005)),
            'watch_time_seconds' => $views * fake()->numberBetween(30, 300),
            'avg_view_duration_seconds' => fake()->numberBetween(30, 600),
            // Not exposed by the YouTube Analytics API; null mirrors production data.
            'impressions' => null,
            'ctr' => null,
            'subscribers_gained' => fake()->numberBetween(0, 500),
            'native_metric_payload' => null,
        ];
    }
}
