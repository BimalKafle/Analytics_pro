<?php

namespace Tests\Feature\Platform;

use App\Exceptions\PlatformApiException;
use App\Models\PlatformAccount;
use App\Services\PlatformTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlatformTokenServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.youtube.client_id' => 'test-client-id',
            'services.youtube.client_secret' => 'test-client-secret',
            'services.youtube.redirect_uri' => 'http://localhost:8000/api/youtube/callback',
        ]);
    }

    public function test_returns_current_token_when_still_valid(): void
    {
        Http::fake();
        $account = PlatformAccount::factory()->create([
            'access_token' => 'still-valid-token',
            'token_expires_at' => now()->addHour(),
        ]);

        $token = app(PlatformTokenService::class)->getValidAccessToken($account);

        $this->assertSame('still-valid-token', $token);
        Http::assertNothingSent();
    }

    public function test_refreshes_expired_token_and_persists_it(): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'new-access-token',
                'expires_in' => 3599,
            ]),
        ]);
        $account = PlatformAccount::factory()->create([
            'access_token' => 'expired-token',
            'refresh_token' => 'stored-refresh-token',
            'token_expires_at' => now()->subMinute(),
        ]);

        $token = app(PlatformTokenService::class)->getValidAccessToken($account);

        $this->assertSame('new-access-token', $token);

        $account->refresh();
        $this->assertSame('new-access-token', $account->access_token);
        // Google omits the refresh token on refresh responses; the stored one must survive.
        $this->assertSame('stored-refresh-token', $account->refresh_token);
        $this->assertTrue($account->token_expires_at->isFuture());
    }

    public function test_throws_when_expired_without_refresh_token(): void
    {
        Http::fake();
        $account = PlatformAccount::factory()->create([
            'refresh_token' => null,
            'token_expires_at' => now()->subMinute(),
        ]);

        $this->expectException(PlatformApiException::class);

        app(PlatformTokenService::class)->getValidAccessToken($account);
    }
}
