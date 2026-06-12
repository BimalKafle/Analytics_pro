<?php

namespace App\Services\Platform;

use App\Enums\Platform;
use App\Services\Platform\YouTube\YouTubeProvider;

class PlatformProviderFactory
{
    public function make(Platform $platform): PlatformProvider
    {
        return match ($platform) {
            Platform::YouTube => new YouTubeProvider(
                clientId: (string) config('services.youtube.client_id'),
                clientSecret: (string) config('services.youtube.client_secret'),
                redirectUri: (string) config('services.youtube.redirect_uri'),
            ),
        };
    }
}
