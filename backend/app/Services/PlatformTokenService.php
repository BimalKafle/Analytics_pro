<?php

namespace App\Services;

use App\Exceptions\PlatformApiException;
use App\Models\PlatformAccount;
use App\Services\Platform\PlatformProviderFactory;

/**
 * Supplies a valid access token for a platform account,
 * transparently refreshing expired tokens.
 */
class PlatformTokenService
{
    /**
     * Refresh this many seconds before actual expiry to avoid
     * using a token that dies mid-request.
     */
    private const EXPIRY_LEEWAY_SECONDS = 60;

    public function __construct(private readonly PlatformProviderFactory $providerFactory) {}

    /**
     * @throws PlatformApiException when the token cannot be refreshed
     */
    public function getValidAccessToken(PlatformAccount $account): string
    {
        if (! $this->isExpired($account)) {
            return $account->access_token;
        }

        if ($account->refresh_token === null) {
            throw new PlatformApiException('Platform connection expired and cannot be refreshed. Please reconnect.');
        }

        $tokens = $this->providerFactory
            ->make($account->platform)
            ->refreshAccessToken($account->refresh_token);

        $account->update([
            'access_token' => $tokens->accessToken,
            'refresh_token' => $tokens->refreshToken,
            'token_expires_at' => $tokens->expiresAt,
        ]);

        return $tokens->accessToken;
    }

    private function isExpired(PlatformAccount $account): bool
    {
        return $account->token_expires_at !== null
            && $account->token_expires_at->subSeconds(self::EXPIRY_LEEWAY_SECONDS)->isPast();
    }
}
