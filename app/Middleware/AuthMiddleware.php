<?php

namespace App\Middleware;

use Core\Middleware\Middleware;
use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Only signed-in users pass; others go to the login page and come back afterwards.
 */
class AuthMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!Session::get('user')) {
            Session::set('intended', $request->method() === 'GET' ? $request->path() : '/');
            Session::flash('error', 'Please log in first.');
            return Response::redirect('/login');
        }
        return $next($request);
    }
}
