<?php

namespace Database\Factories;

use App\Enums\Platform;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PlatformAccount>
 */
class PlatformAccountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'platform' => Platform::YouTube,
            'channel_id' => 'UC'.fake()->regexify('[A-Za-z0-9_-]{22}'),
            'channel_name' => fake()->company(),
            'access_token' => fake()->sha256(),
            'refresh_token' => fake()->sha256(),
            'token_expires_at' => now()->addHour(),
            'connected_at' => now(),
        ];
    }
}
