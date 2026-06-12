<?php

namespace App\Http\Resources;

use App\Models\PlatformAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PlatformAccount
 */
class PlatformAccountResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'platform' => $this->platform,
            'channel_id' => $this->channel_id,
            'channel_name' => $this->channel_name,
            'connected_at' => $this->connected_at,
            'video_count' => $this->whenCounted('videos'),
        ];
    }
}
