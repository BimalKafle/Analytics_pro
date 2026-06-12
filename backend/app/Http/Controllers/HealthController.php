<?php

namespace App\Http\Controllers;

use App\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class HealthController extends Controller
{
    public function __invoke(HealthCheckService $healthCheckService): JsonResponse
    {
        $dependencies = $healthCheckService->checkDependencies();
        $isHealthy = ! in_array(false, $dependencies, true);

        return response()->json(
            [
                'status' => $isHealthy ? 'ok' : 'degraded',
                'dependencies' => $dependencies,
            ],
            $isHealthy ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE,
        );
    }
}
