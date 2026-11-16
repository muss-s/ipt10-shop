<?php

namespace Core\Middleware;

use Core\Request;
use Core\Response;

/**
 * A middleware inspects the request and either returns a Response itself
 * (e.g. redirect to login) or passes it on with $next($request).
 */
interface Middleware
{
    public function handle(Request $request, callable $next): Response;
}
