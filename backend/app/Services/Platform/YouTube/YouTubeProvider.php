<?php

namespace App\Services\Platform\YouTube;

use App\Exceptions\PlatformApiException;
use App\Services\Platform\ChannelInfo;
use App\Services\Platform\OAuthTokens;
use App\Services\Platform\PlatformProvider;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeProvider implements PlatformProvider
{
    private const AUTHORIZATION_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const CHANNELS_URL = 'https://www.googleapis.com/youtube/v3/channels';

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
