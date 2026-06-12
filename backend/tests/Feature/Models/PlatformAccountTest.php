<?php

namespace Tests\Feature\Models;

use App\Models\PlatformAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PlatformAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_tokens_are_encrypted_at_rest(): void
    {
        $account = PlatformAccount::factory()->create([
            'access_token' => 'plain-access-token',
            'refresh_token' => 'plain-refresh-token',
        ]);

        $rawRow = DB::table('platform_accounts')->where('id', $account->id)->first();

        $this->assertNotSame('plain-access-token', $rawRow->access_token);
        $this->assertNotSame('plain-refresh-token', $rawRow->refresh_token);
        $this->assertSame('plain-access-token', $account->fresh()->access_token);
        $this->assertSame('plain-refresh-token', $account->fresh()->refresh_token);
    }

    public function test_tokens_are_hidden_from_serialization(): void
    {
        $serialized = PlatformAccount::factory()->create()->toArray();

        $this->assertArrayNotHasKey('access_token', $serialized);
        $this->assertArrayNotHasKey('refresh_token', $serialized);
    }

    public function test_duplicate_channel_connection_is_rejected(): void
    {
        $account = PlatformAccount::factory()->create();

        $this->expectException(\Illuminate\Database\QueryException::class);

        PlatformAccount::factory()->create([
            'user_id' => $account->user_id,
            'platform' => $account->platform,
            'channel_id' => $account->channel_id,
        ]);
    }
}
