<?php

namespace App\Middleware;

use Core\Logger;
use Core\Middleware\Middleware;
use Core\Request;
use Core\Response;

/**
 * Logs method, path, status and time taken for every request.
 */
class RequestLogMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $start = microtime(true);
        $response = $next($request);
        $ms = round((microtime(true) - $start) * 1000, 1);
        Logger::info(sprintf('%s %s %d %sms %s', $request->method(), $request->path(), $response->status(), $ms, $request->ip()));
        return $response;
    }
}
