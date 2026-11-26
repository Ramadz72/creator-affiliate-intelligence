<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRequestSizeIsAllowed
{
    public function handle(Request $request, Closure $next): Response
    {
        $maxBytes = (int) config('app.max_request_bytes', 25 * 1024 * 1024);
        $contentLength = $request->header('Content-Length');

        if (
            $contentLength !== null
            && is_numeric($contentLength)
            && (int) $contentLength > $maxBytes
        ) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Payload too large.'], 413);
            }

            abort(413, 'Payload too large.');
        }

        return $next($request);
    }
}
