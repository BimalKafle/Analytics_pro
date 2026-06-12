<?php

namespace App\Services\Platform;

use App\Exceptions\PlatformApiException;

/**
 * Contract every platform integration must implement.
 *
 * Adding a new platform (TikTok, Instagram, ...) means implementing this
 * interface and registering it in PlatformProviderFactory - no changes
 * to existing providers or services.
 */
interface PlatformProvider
{
    /**
     * Build the URL the creator is redirected to for OAuth consent.
     */
    public function buildAuthorizationUrl(string $state): string;

    /**
     * Exchange an authorization code for access/refresh tokens.
     *
     * @throws PlatformApiException
     */
    public function exchangeAuthorizationCode(string $code): OAuthTokens;

    /**
     * Obtain a fresh access token using a refresh token.
     *
     * @throws PlatformApiException
     */
    public function refreshAccessToken(string $refreshToken): OAuthTokens;

    /**
     * Fetch the connected channel's identity.
     *
     * @throws PlatformApiException
     */
    public function fetchChannelInfo(string $accessToken): ChannelInfo;

    /**
     * Fetch metadata for all videos of the connected channel.
     *
     * @return list<VideoData>
     *
     * @throws PlatformApiException
     */
    public function fetchVideos(string $accessToken): array;
}
