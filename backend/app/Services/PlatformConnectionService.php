<?php

namespace App\Services;

use App\Enums\Platform;
use App\Exceptions\InvalidOAuthStateException;
use App\Models\PlatformAccount;
use App\Models\User;
use App\Services\Platform\PlatformProviderFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Platform-agnostic OAuth connection flow: works for any platform
 * with a registered provider.
 */
class PlatformConnectionService
{
    private const STATE_TTL_SECONDS = 600;

    public function __construct(private readonly PlatformProviderFactory $providerFactory) {}

    /**
     * Begin the OAuth flow: bind a one-time state token to the user and
     * return the platform consent URL to redirect the creator to.
     */
    public function startConnection(User $user, Platform $platform): string
    {
        $state = Str::random(40);

        Cache::put($this->stateCacheKey($platform, $state), $user->id, self::STATE_TTL_SECONDS);

        return $this->providerFactory->make($platform)->buildAuthorizationUrl($state);
    }

    /**
     * Complete the OAuth flow from the platform callback.
     *
     * @throws InvalidOAuthStateException when the state is unknown or expired
     */
    public function completeConnection(Platform $platform, string $state, string $code): PlatformAccount
    {
        $userId = Cache::pull($this->stateCacheKey($platform, $state));

        if ($userId === null) {
            throw new InvalidOAuthStateException('OAuth state is unknown or expired.');
        }

        $provider = $this->providerFactory->make($platform);
        $tokens = $provider->exchangeAuthorizationCode($code);
        $channel = $provider->fetchChannelInfo($tokens->accessToken);

        return PlatformAccount::updateOrCreate(
            [
                'user_id' => $userId,
                'platform' => $platform,
                'channel_id' => $channel->id,
            ],
            [
                'channel_name' => $channel->name,
                'access_token' => $tokens->accessToken,
                'refresh_token' => $tokens->refreshToken,
                'token_expires_at' => $tokens->expiresAt,
                'connected_at' => now(),
            ],
        );
    }

    /**
     * Remove the creator's connection for the given platform.
     * Imported videos and snapshots are removed with it (cascade).
     */
    public function disconnect(User $user, Platform $platform): bool
    {
        return $user->platformAccounts()
            ->where('platform', $platform)
            ->delete() > 0;
    }

    private function stateCacheKey(Platform $platform, string $state): string
    {
        return "oauth_state:{$platform->value}:{$state}";
    }
}
