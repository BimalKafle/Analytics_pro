<?php

namespace App\Services\Platform;

use Carbon\CarbonImmutable;

/**
 * OAuth token set returned by a platform after authorization or refresh.
 */
final readonly class OAuthTokens
{
    public function __construct(
        public string $accessToken,
        public ?string $refreshToken,
        public ?CarbonImmutable $expiresAt,
    ) {}
}
