<?php

namespace App\Models;

use App\Enums\Platform;
use Database\Factories\PlatformAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformAccount extends Model
{
    /** @use HasFactory<PlatformAccountFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'channel_id',
        'channel_name',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'connected_at',
    ];

    /**
     * OAuth tokens must never be exposed through serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'platform' => Platform::class,
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'connected_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Video, $this>
     */
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }
}
