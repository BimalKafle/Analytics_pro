<?php

namespace App\Console\Commands;

use App\Jobs\ImportVideosJob;
use App\Models\PlatformAccount;
use Illuminate\Console\Command;

class SyncAllVideosCommand extends Command
{
    protected $signature = 'videos:sync-all';

    protected $description = 'Queue a video metadata import for every connected platform account';

    public function handle(): int
    {
        $dispatchedCount = 0;

        PlatformAccount::query()->chunkById(100, function ($accounts) use (&$dispatchedCount): void {
            foreach ($accounts as $account) {
                ImportVideosJob::dispatch($account);
                $dispatchedCount++;
            }
        });

        $this->info("Queued video import for {$dispatchedCount} platform account(s).");

        return self::SUCCESS;
    }
}
