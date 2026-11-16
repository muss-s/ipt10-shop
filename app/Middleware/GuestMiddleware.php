<?php

namespace App\Middleware;

use Core\Middleware\Middleware;
use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Keeps signed-in users away from login/register pages.
 */
class GuestMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (Session::get('user')) {
            return Response::redirect('/');
        }
        return $next($request);
    }
}
