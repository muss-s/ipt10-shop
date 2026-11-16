<?php

namespace App\Middleware;

use Core\HttpException;
use Core\Middleware\Middleware;
use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Only users with role "admin" pass. Use after "auth".
 */
class AdminMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if ((Session::get('user')['role'] ?? null) !== 'admin') {
            throw new HttpException(403, 'Admins only.');
        }
        return $next($request);
    }
}
