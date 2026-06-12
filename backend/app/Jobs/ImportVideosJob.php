<?php

namespace App\Jobs;

use App\Models\PlatformAccount;
use App\Services\VideoImportService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportVideosJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> seconds between retries */
    public array $backoff = [60, 300];

    public function __construct(public readonly PlatformAccount $account) {}

    /**
     * Prevent overlapping imports for the same account.
     */
    public function uniqueId(): string
    {
        return (string) $this->account->id;
    }

    public function handle(VideoImportService $importService): void
    {
        $importedCount = $importService->importForAccount($this->account);

        Log::info('Video import completed.', [
            'platform_account_id' => $this->account->id,
            'platform' => $this->account->platform->value,
            'video_count' => $importedCount,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Video import failed permanently.', [
            'platform_account_id' => $this->account->id,
            'platform' => $this->account->platform->value,
            'error' => $exception?->getMessage(),
        ]);
    }
}
