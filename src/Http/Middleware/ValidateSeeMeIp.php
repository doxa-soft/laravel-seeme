<?php

namespace DoxaSoft\LaravelSeeMe\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSeeMeIp
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = config('seeme.webhooks.allowed_ips');

        if (empty($allowedIps)) {
            return $next($request);
        }

        $allowed = array_map('trim', explode(',', $allowedIps));

        if (! in_array($request->ip(), $allowed, strict: true)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
