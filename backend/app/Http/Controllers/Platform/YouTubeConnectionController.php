<?php

namespace App\Http\Controllers\Platform;

use App\Enums\Platform;
use App\Exceptions\InvalidOAuthStateException;
use App\Exceptions\PlatformApiException;
use App\Http\Controllers\Controller;
use App\Jobs\ImportVideosJob;
use App\Services\PlatformConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class YouTubeConnectionController extends Controller
{
    public function __construct(private readonly PlatformConnectionService $connectionService) {}

    /**
     * Return the Google consent URL for the frontend to redirect to.
     */
    public function connect(Request $request): JsonResponse
    {
        $authorizationUrl = $this->connectionService->startConnection($request->user(), Platform::YouTube);

        return response()->json([
            'data' => ['authorization_url' => $authorizationUrl],
        ]);
    }

    /**
     * OAuth callback hit by Google's redirect. Always sends the creator
     * back to the frontend with a success or error query flag.
     */
    public function callback(Request $request): RedirectResponse
    {
        if ($request->query('error') !== null || $request->query('code') === null) {
            return $this->redirectToFrontend(['youtube' => 'denied']);
        }

        try {
            $this->connectionService->completeConnection(
                Platform::YouTube,
                state: (string) $request->query('state'),
                code: (string) $request->query('code'),
            );
        } catch (InvalidOAuthStateException) {
            return $this->redirectToFrontend(['youtube' => 'invalid_state']);
        } catch (PlatformApiException) {
            return $this->redirectToFrontend(['youtube' => 'failed']);
        }

        return $this->redirectToFrontend(['youtube' => 'connected']);
    }

    /**
     * Queue a fresh video import for the creator's YouTube account(s).
     */
    public function sync(Request $request): JsonResponse
    {
        $accounts = $request->user()
            ->platformAccounts()
            ->where('platform', Platform::YouTube)
            ->get();

        if ($accounts->isEmpty()) {
            return response()->json(['message' => __('No YouTube connection found.')], 404);
        }

        foreach ($accounts as $account) {
            ImportVideosJob::dispatch($account);
        }

        return response()->json(['message' => __('Video import has been queued.')], 202);
    }

    public function disconnect(Request $request): JsonResponse
    {
        $removed = $this->connectionService->disconnect($request->user(), Platform::YouTube);

        if (! $removed) {
            return response()->json(['message' => __('No YouTube connection found.')], 404);
        }

        return response()->json(['message' => __('YouTube account disconnected.')]);
    }

    /**
     * @param  array<string, string>  $query
     */
    private function redirectToFrontend(array $query): RedirectResponse
    {
        $base = rtrim((string) config('app.frontend_url'), '/');

        return redirect()->away($base.'/account?'.http_build_query($query));
    }
}
