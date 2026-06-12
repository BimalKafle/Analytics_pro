<?php

namespace App\Services\Platform\YouTube;

use App\Exceptions\PlatformApiException;
use App\Services\Platform\ChannelInfo;
use App\Services\Platform\OAuthTokens;
use App\Services\Platform\PlatformProvider;
use App\Services\Platform\VideoData;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeProvider implements PlatformProvider
{
    private const AUTHORIZATION_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const CHANNELS_URL = 'https://www.googleapis.com/youtube/v3/channels';

    private const PLAYLIST_ITEMS_URL = 'https://www.googleapis.com/youtube/v3/playlistItems';

    private const VIDEOS_URL = 'https://www.googleapis.com/youtube/v3/videos';

    private const PAGE_SIZE = 50;

    /**
     * Safety cap on playlist pagination (50 videos per page).
     */
    private const MAX_PAGES = 40;

    /**
     * Read-only access to video data and analytics reports.
     */
    private const SCOPES = [
        'https://www.googleapis.com/auth/youtube.readonly',
        'https://www.googleapis.com/auth/yt-analytics.readonly',
    ];

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $redirectUri,
    ) {}

    public function buildAuthorizationUrl(string $state): string
    {
        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', self::SCOPES),
            // Offline access + consent prompt are required to receive a refresh token.
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        return self::AUTHORIZATION_URL.'?'.$query;
    }

    public function exchangeAuthorizationCode(string $code): OAuthTokens
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri,
        ]);

        $this->ensureSuccessful($response, 'token exchange');

        return $this->toOAuthTokens($response->json());
    }

    public function refreshAccessToken(string $refreshToken): OAuthTokens
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        $this->ensureSuccessful($response, 'token refresh');

        // Google does not return a new refresh token on refresh; keep the old one.
        return $this->toOAuthTokens($response->json(), fallbackRefreshToken: $refreshToken);
    }

    public function fetchChannelInfo(string $accessToken): ChannelInfo
    {
        $response = Http::withToken($accessToken)->get(self::CHANNELS_URL, [
            'part' => 'snippet',
            'mine' => 'true',
        ]);

        $this->ensureSuccessful($response, 'channel lookup');

        $channel = $response->json('items.0');

        if ($channel === null) {
            throw new PlatformApiException('The Google account has no YouTube channel.');
        }

        return new ChannelInfo(
            id: $channel['id'],
            name: $channel['snippet']['title'] ?? null,
        );
    }

    public function fetchVideos(string $accessToken): array
    {
        $uploadsPlaylistId = $this->fetchUploadsPlaylistId($accessToken);
        $videoIds = $this->fetchAllVideoIds($accessToken, $uploadsPlaylistId);

        $videos = [];

        foreach (array_chunk($videoIds, self::PAGE_SIZE) as $idChunk) {
            $response = Http::withToken($accessToken)->get(self::VIDEOS_URL, [
                'part' => 'snippet,contentDetails',
                'id' => implode(',', $idChunk),
                'maxResults' => self::PAGE_SIZE,
            ]);

            $this->ensureSuccessful($response, 'video metadata fetch');

            foreach ($response->json('items', []) as $item) {
                $videos[] = $this->toVideoData($item);
            }
        }

        return $videos;
    }

    private function fetchUploadsPlaylistId(string $accessToken): string
    {
        $response = Http::withToken($accessToken)->get(self::CHANNELS_URL, [
            'part' => 'contentDetails',
            'mine' => 'true',
        ]);

        $this->ensureSuccessful($response, 'uploads playlist lookup');

        $playlistId = $response->json('items.0.contentDetails.relatedPlaylists.uploads');

        if ($playlistId === null) {
            throw new PlatformApiException('The YouTube channel has no uploads playlist.');
        }

        return $playlistId;
    }

    /**
     * @return list<string>
     */
    private function fetchAllVideoIds(string $accessToken, string $uploadsPlaylistId): array
    {
        $videoIds = [];
        $pageToken = null;
        $page = 0;

        do {
            $response = Http::withToken($accessToken)->get(self::PLAYLIST_ITEMS_URL, array_filter([
                'part' => 'contentDetails',
                'playlistId' => $uploadsPlaylistId,
                'maxResults' => self::PAGE_SIZE,
                'pageToken' => $pageToken,
            ]));

            $this->ensureSuccessful($response, 'playlist items fetch');

            foreach ($response->json('items', []) as $item) {
                $videoIds[] = $item['contentDetails']['videoId'];
            }

            $pageToken = $response->json('nextPageToken');
            $page++;
        } while ($pageToken !== null && $page < self::MAX_PAGES);

        if ($pageToken !== null) {
            Log::info('YouTube video import truncated at pagination cap.', [
                'imported_count' => count($videoIds),
                'max_pages' => self::MAX_PAGES,
            ]);
        }

        return $videoIds;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function toVideoData(array $item): VideoData
    {
        $snippet = $item['snippet'] ?? [];
        $thumbnails = $snippet['thumbnails'] ?? [];
        // Prefer higher-resolution thumbnails when available.
        $thumbnail = $thumbnails['high'] ?? $thumbnails['medium'] ?? $thumbnails['default'] ?? null;

        return new VideoData(
            platformVideoId: $item['id'],
            title: $snippet['title'] ?? '',
            description: $snippet['description'] ?? null,
            thumbnailUrl: $thumbnail['url'] ?? null,
            embedUrl: "https://www.youtube.com/embed/{$item['id']}",
            publishedAt: CarbonImmutable::parse($snippet['publishedAt'] ?? now()),
            durationSeconds: $this->parseIsoDuration($item['contentDetails']['duration'] ?? null),
        );
    }

    /**
     * Convert an ISO 8601 duration (e.g. "PT1H2M3S") to whole seconds.
     */
    private function parseIsoDuration(?string $isoDuration): ?int
    {
        if ($isoDuration === null) {
            return null;
        }

        try {
            return (int) CarbonInterval::make($isoDuration)?->totalSeconds;
        } catch (\Throwable) {
            Log::warning('Unparseable YouTube video duration.', ['duration' => $isoDuration]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function toOAuthTokens(array $payload, ?string $fallbackRefreshToken = null): OAuthTokens
    {
        return new OAuthTokens(
            accessToken: $payload['access_token'],
            refreshToken: $payload['refresh_token'] ?? $fallbackRefreshToken,
            expiresAt: isset($payload['expires_in'])
                ? CarbonImmutable::now()->addSeconds((int) $payload['expires_in'])
                : null,
        );
    }

    /**
     * @throws PlatformApiException
     */
    private function ensureSuccessful(Response $response, string $operation): void
    {
        if ($response->successful()) {
            return;
        }

        // Log the error class only - response bodies on token endpoints can
        // echo request details and must never reach the logs verbatim.
        Log::warning("YouTube {$operation} failed.", [
            'status' => $response->status(),
            'google_error' => $response->json('error'),
        ]);

        throw new PlatformApiException("YouTube {$operation} failed.");
    }
}
