<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

class HealthCheckService
{
    /**
     * Check connectivity of the application's critical dependencies.
     *
     * @return array{database: bool, redis: bool}
     */
    public function checkDependencies(): array
    {
        return [
            'database' => $this->isDatabaseReachable(),
            'redis' => $this->isRedisReachable(),
        ];
    }

    private function isDatabaseReachable(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable $exception) {
            Log::warning('Health check: database unreachable.', ['error' => $exception->getMessage()]);

            return false;
        }
    }

    private function isRedisReachable(): bool
    {
        try {
            Redis::connection()->ping();

            return true;
        } catch (Throwable $exception) {
            Log::warning('Health check: redis unreachable.', ['error' => $exception->getMessage()]);

            return false;
        }
    }
}
