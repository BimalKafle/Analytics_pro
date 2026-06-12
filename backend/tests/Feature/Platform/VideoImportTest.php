<?php

namespace Tests\Feature\Platform;

use App\Jobs\ImportVideosJob;
use App\Models\PlatformAccount;
use App\Models\User;
use App\Models\Video;
use App\Services\VideoImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class VideoImportTest extends TestCase
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

    /**
     * Fake the three YouTube read endpoints with two pages of playlist items.
     */
    private function fakeYouTubeVideoApis(): void
    {
        Http::fake([
            'www.googleapis.com/youtube/v3/channels*' => Http::response([
                'items' => [[
                    'id' => 'UC-test-channel',
                    'snippet' => ['title' => 'Test Channel'],
                    'contentDetails' => ['relatedPlaylists' => ['uploads' => 'UU-uploads']],
                ]],
            ]),
            'www.googleapis.com/youtube/v3/playlistItems*' => Http::sequence()
                ->push([
                    'items' => [
                        ['contentDetails' => ['videoId' => 'video-aaa']],
                        ['contentDetails' => ['videoId' => 'video-bbb']],
                    ],
                    'nextPageToken' => 'page-2',
                ])
                ->push([
                    'items' => [
                        ['contentDetails' => ['videoId' => 'video-ccc']],
                    ],
                ]),
            'www.googleapis.com/youtube/v3/videos*' => Http::response([
                'items' => [
                    [
                        'id' => 'video-aaa',
                        'snippet' => [
                            'title' => 'First Video',
                            'description' => 'Description A',
                            'publishedAt' => '2026-01-15T10:00:00Z',
                            'thumbnails' => ['high' => ['url' => 'https://i.ytimg.com/vi/video-aaa/hq.jpg']],
                        ],
                        'contentDetails' => ['duration' => 'PT1H2M3S'],
                    ],
                    [
                        'id' => 'video-bbb',
                        'snippet' => [
                            'title' => 'Second Video',
                            'publishedAt' => '2026-02-20T12:30:00Z',
                            'thumbnails' => ['default' => ['url' => 'https://i.ytimg.com/vi/video-bbb/default.jpg']],
                        ],
                        'contentDetails' => ['duration' => 'PT45S'],
                    ],
                    [
                        'id' => 'video-ccc',
                        'snippet' => [
                            'title' => 'Third Video',
                            'publishedAt' => '2026-03-01T08:00:00Z',
                        ],
                        'contentDetails' => ['duration' => 'PT10M'],
                    ],
                ],
            ]),
        ]);
    }

    public function test_import_creates_videos_with_normalized_metadata(): void
    {
        $this->fakeYouTubeVideoApis();
        $account = PlatformAccount::factory()->create(['token_expires_at' => now()->addHour()]);

        $count = app(VideoImportService::class)->importForAccount($account);

        $this->assertSame(3, $count);
        $this->assertDatabaseCount('videos', 3);

        $video = Video::where('platform_video_id', 'video-aaa')->firstOrFail();
        $this->assertSame('First Video', $video->title);
        $this->assertSame('Description A', $video->description);
        $this->assertSame('https://i.ytimg.com/vi/video-aaa/hq.jpg', $video->thumbnail_url);
        $this->assertSame('https://www.youtube.com/embed/video-aaa', $video->embed_url);
        $this->assertSame(3723, $video->duration_seconds); // PT1H2M3S
        $this->assertSame('2026-01-15 10:00:00', $video->published_at->toDateTimeString());
    }

    public function test_reimport_updates_existing_videos_without_duplicates(): void
    {
        $this->fakeYouTubeVideoApis();
        $account = PlatformAccount::factory()->create(['token_expires_at' => now()->addHour()]);
        Video::factory()->for($account, 'platformAccount')->create([
            'platform_video_id' => 'video-aaa',
            'title' => 'Old Title',
        ]);

        app(VideoImportService::class)->importForAccount($account);

        $this->assertDatabaseCount('videos', 3);
        $this->assertSame('First Video', Video::where('platform_video_id', 'video-aaa')->value('title'));
    }

    public function test_successful_oauth_callback_queues_video_import(): void
    {
        Queue::fake();
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'google-access-token',
                'refresh_token' => 'google-refresh-token',
                'expires_in' => 3599,
            ]),
            'www.googleapis.com/youtube/v3/channels*' => Http::response([
                'items' => [['id' => 'UC-test-channel', 'snippet' => ['title' => 'Test Channel']]],
            ]),
        ]);
        $user = User::factory()->create();
        $url = $this->actingAs($user)->getJson('/api/youtube/connect')->json('data.authorization_url');
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $this->get("/api/youtube/callback?state={$query['state']}&code=auth-code");

        Queue::assertPushed(ImportVideosJob::class, 1);
    }

    public function test_manual_sync_queues_import_for_own_accounts(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        PlatformAccount::factory()->for($user)->create();

        $this->actingAs($user)->postJson('/api/youtube/sync')->assertStatus(202);

        Queue::assertPushed(ImportVideosJob::class, 1);
    }

    public function test_manual_sync_without_connection_returns_404(): void
    {
        Queue::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/youtube/sync')->assertNotFound();

        Queue::assertNothingPushed();
    }

    public function test_sync_all_command_queues_import_for_every_account(): void
    {
        Queue::fake();
        PlatformAccount::factory()->count(3)->create();

        $this->artisan('videos:sync-all')
            ->expectsOutputToContain('3 platform account(s)')
            ->assertSuccessful();

        Queue::assertPushed(ImportVideosJob::class, 3);
    }

    public function test_platform_accounts_endpoint_lists_own_connections(): void
    {
        $user = User::factory()->create();
        $account = PlatformAccount::factory()->for($user)->create(['channel_name' => 'My Channel']);
        Video::factory()->count(2)->for($account, 'platformAccount')->create();
        PlatformAccount::factory()->create(); // another user's account

        $response = $this->actingAs($user)->getJson('/api/platform-accounts');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.channel_name', 'My Channel')
            ->assertJsonPath('data.0.video_count', 2);

        $this->assertArrayNotHasKey('access_token', $response->json('data.0'));
    }
}
