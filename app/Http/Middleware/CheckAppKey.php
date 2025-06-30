<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckAppKey
{
    public function handle(Request $request, Closure $next)
    {
        $appKey = config('app.key');

        Log::debug('🔑 APP_KEY check', [
            'app_key_set' => !empty($appKey),
            'app_key_length' => $appKey ? strlen($appKey) : 0,
            'route' => $request->path(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => optional($request->user())->id,
            'user_agent' => $request->userAgent(),
        ]);

        if (empty($appKey)) {
            Log::error('❌ APP_KEY is missing at runtime');
        }

        return $next($request);
    }
}
