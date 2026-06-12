<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlatformAccountResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlatformAccountController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $accounts = $request->user()
            ->platformAccounts()
            ->withCount('videos')
            ->orderBy('connected_at')
            ->get();

        return PlatformAccountResource::collection($accounts);
    }
}
