<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRequestSizeIsAllowed
{
    private const MAX_BYTES = 10 * 1024 * 1024;

    public function handle(Request $request, Closure $next): Response
    {
        $contentLength = $request->header('Content-Length');

        if (
            $contentLength !== null
            && is_numeric($contentLength)
            && (int) $contentLength > self::MAX_BYTES
        ) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Payload too large.'], 413);
            }

            abort(413, 'Payload too large.');
        }

        return $next($request);
    }
}
