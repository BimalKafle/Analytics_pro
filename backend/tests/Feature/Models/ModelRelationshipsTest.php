<?php

namespace Tests\Feature\Models;

use App\Models\AnalyticsSnapshot;
use App\Models\PlatformAccount;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_platform_accounts_and_videos_through_them(): void
    {
        $user = User::factory()->create();
        $account = PlatformAccount::factory()->for($user)->create();
        $video = Video::factory()->for($account)->create();

        $this->assertTrue($user->platformAccounts->first()->is($account));
        $this->assertTrue($user->videos->first()->is($video));
    }

    public function test_video_has_analytics_snapshots(): void
    {
        $video = Video::factory()->create();
        $snapshot = AnalyticsSnapshot::factory()->for($video)->create();

        $this->assertTrue($video->analyticsSnapshots->first()->is($snapshot));
        $this->assertTrue($snapshot->video->is($video));
    }

    public function test_snapshot_is_unique_per_video_and_date(): void
    {
        $snapshot = AnalyticsSnapshot::factory()->create();

        $this->expectException(\Illuminate\Database\QueryException::class);

        AnalyticsSnapshot::factory()->create([
            'video_id' => $snapshot->video_id,
            'snapshot_date' => $snapshot->snapshot_date,
        ]);
    }

    public function test_deleting_video_cascades_to_snapshots(): void
    {
        $snapshot = AnalyticsSnapshot::factory()->create();

        $snapshot->video->delete();

        $this->assertDatabaseMissing('analytics_snapshots', ['id' => $snapshot->id]);
    }
}
