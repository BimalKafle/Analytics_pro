<?php

namespace App\Services\Platform;

use Carbon\CarbonImmutable;

/**
 * Video metadata imported from a platform. Only metadata is ever stored -
 * never video files (product rule).
 */
final readonly class VideoData
{
    public function __construct(
        public string $platformVideoId,
        public string $title,
        public ?string $description,
        public ?string $thumbnailUrl,
        public string $embedUrl,
        public CarbonImmutable $publishedAt,
        public ?int $durationSeconds,
    ) {}
}
