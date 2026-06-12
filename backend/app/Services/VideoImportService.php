<?php

namespace App\Services;

use App\Exceptions\PlatformApiException;
use App\Models\PlatformAccount;
use App\Services\Platform\PlatformProviderFactory;

/**
 * Imports video metadata for a platform account. Re-running is safe:
 * existing videos are updated, never duplicated, and videos removed
 * from the platform are kept locally (historical analytics remain valid).
 */
class VideoImportService
{
    public function __construct(
        private readonly PlatformProviderFactory $providerFactory,
        private readonly PlatformTokenService $tokenService,
    ) {}

    /**
     * @return int number of videos imported or updated
     *
     * @throws PlatformApiException
     */
    public function importForAccount(PlatformAccount $account): int
    {
        $accessToken = $this->tokenService->getValidAccessToken($account);
        $videos = $this->providerFactory->make($account->platform)->fetchVideos($accessToken);

        foreach ($videos as $videoData) {
            $account->videos()->updateOrCreate(
                ['platform_video_id' => $videoData->platformVideoId],
                [
                    'title' => $videoData->title,
                    'description' => $videoData->description,
                    'thumbnail_url' => $videoData->thumbnailUrl,
                    'embed_url' => $videoData->embedUrl,
                    'published_at' => $videoData->publishedAt,
                    'duration_seconds' => $videoData->durationSeconds,
                ],
            );
        }

        return count($videos);
    }
}
