<?php

namespace App\Middleware;

use Core\Middleware\Middleware;
use Core\Request;
use Core\Response;
use Core\Session;

class SessionMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        Session::start();
        return $next($request);
    }
}
