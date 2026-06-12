<?php

namespace Tests\Feature\Platform;

use App\Models\PlatformAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class YouTubeConnectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.youtube.client_id' => 'test-client-id',
            'services.youtube.client_secret' => 'test-client-secret',
            'services.youtube.redirect_uri' => 'http://localhost:8000/api/youtube/callback',
            'app.frontend_url' => 'http://localhost:3000',
        ]);
    }

    private function fakeGoogleApis(): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'google-access-token',
                'refresh_token' => 'google-refresh-token',
                'expires_in' => 3599,
            ]),
            'www.googleapis.com/youtube/v3/channels*' => Http::response([
                'items' => [
                    ['id' => 'UC-test-channel', 'snippet' => ['title' => 'Test Channel']],
                ],
            ]),
        ]);
    }

    private function authorizationState(User $user): string
    {
        $url = $this->actingAs($user)
            ->getJson('/api/youtube/connect')
            ->json('data.authorization_url');

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return (string) $query['state'];
    }

    public function test_connect_returns_google_authorization_url(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/youtube/connect');

        $response->assertOk();

        $url = $response->json('data.authorization_url');
        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/v2/auth?', $url);
        $this->assertStringContainsString('client_id=test-client-id', $url);
        $this->assertStringContainsString('access_type=offline', $url);
        $this->assertStringContainsString('youtube.readonly', urldecode($url));
        $this->assertStringContainsString('state=', $url);
    }

    public function test_connect_requires_verified_email(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->getJson('/api/youtube/connect')->assertForbidden();
    }

    public function test_connect_requires_authentication(): void
    {
        $this->getJson('/api/youtube/connect')->assertUnauthorized();
    }

    public function test_callback_creates_platform_account_and_redirects(): void
    {
        $this->fakeGoogleApis();
        $user = User::factory()->create();
        $state = $this->authorizationState($user);

        $response = $this->get("/api/youtube/callback?state={$state}&code=auth-code");

        $response->assertRedirect('http://localhost:3000/account?youtube=connected');

        $account = PlatformAccount::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('UC-test-channel', $account->channel_id);
        $this->assertSame('Test Channel', $account->channel_name);
        $this->assertSame('google-access-token', $account->access_token);
        $this->assertSame('google-refresh-token', $account->refresh_token);
        $this->assertNotNull($account->token_expires_at);
    }

    public function test_callback_rejects_unknown_state(): void
    {
        $this->fakeGoogleApis();

        $response = $this->get('/api/youtube/callback?state=forged-state&code=auth-code');

        $response->assertRedirect('http://localhost:3000/account?youtube=invalid_state');
        $this->assertDatabaseCount('platform_accounts', 0);
    }

    public function test_callback_state_is_single_use(): void
    {
        $this->fakeGoogleApis();
        $user = User::factory()->create();
        $state = $this->authorizationState($user);

        $this->get("/api/youtube/callback?state={$state}&code=auth-code");
        $secondAttempt = $this->get("/api/youtube/callback?state={$state}&code=auth-code");

        $secondAttempt->assertRedirect('http://localhost:3000/account?youtube=invalid_state');
    }

    public function test_callback_handles_user_denial(): void
    {
        $response = $this->get('/api/youtube/callback?error=access_denied');

        $response->assertRedirect('http://localhost:3000/account?youtube=denied');
    }

    public function test_callback_handles_google_api_failure(): void
    {
        Http::fake(['oauth2.googleapis.com/token' => Http::response(['error' => 'invalid_grant'], 400)]);
        $user = User::factory()->create();
        $state = $this->authorizationState($user);

        $response = $this->get("/api/youtube/callback?state={$state}&code=bad-code");

        $response->assertRedirect('http://localhost:3000/account?youtube=failed');
        $this->assertDatabaseCount('platform_accounts', 0);
    }

    public function test_reconnecting_same_channel_updates_existing_account(): void
    {
        $this->fakeGoogleApis();
        $user = User::factory()->create();

        $firstState = $this->authorizationState($user);
        $this->get("/api/youtube/callback?state={$firstState}&code=auth-code");

        $secondState = $this->authorizationState($user);
        $this->get("/api/youtube/callback?state={$secondState}&code=auth-code");

        $this->assertSame(1, PlatformAccount::where('user_id', $user->id)->count());
    }

    public function test_disconnect_removes_account(): void
    {
        $user = User::factory()->create();
        PlatformAccount::factory()->for($user)->create();

        $this->actingAs($user)->deleteJson('/api/youtube/disconnect')->assertOk();

        $this->assertDatabaseCount('platform_accounts', 0);
    }

    public function test_disconnect_without_connection_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->deleteJson('/api/youtube/disconnect')->assertNotFound();
    }

    public function test_disconnect_only_removes_own_account(): void
    {
        $user = User::factory()->create();
        $otherAccount = PlatformAccount::factory()->create();

        $this->actingAs($user)->deleteJson('/api/youtube/disconnect')->assertNotFound();

        $this->assertModelExists($otherAccount);
    }
}
