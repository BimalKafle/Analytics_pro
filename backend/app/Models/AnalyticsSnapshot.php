<?php

namespace App\Models;

use Database\Factories\AnalyticsSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsSnapshot extends Model
{
    /** @use HasFactory<AnalyticsSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'video_id',
        'snapshot_date',
        'views',
        'likes',
        'comments',
        'shares',
        'watch_time_seconds',
        'avg_view_duration_seconds',
        'impressions',
        'ctr',
        'subscribers_gained',
        'native_metric_payload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'views' => 'integer',
            'likes' => 'integer',
            'comments' => 'integer',
            'shares' => 'integer',
            'watch_time_seconds' => 'integer',
            'avg_view_duration_seconds' => 'integer',
            'impressions' => 'integer',
            'ctr' => 'decimal:4',
            'subscribers_gained' => 'integer',
            'native_metric_payload' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Video, $this>
     */
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
