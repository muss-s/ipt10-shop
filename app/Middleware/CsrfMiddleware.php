<?php

namespace App\Middleware;

use Core\HttpException;
use Core\Middleware\Middleware;
use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Rejects state-changing requests that lack the session's CSRF token.
 */
class CsrfMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $token = (string) $request->input('_token', '');
            $expected = (string) Session::get('_token', '');
            if ($expected === '' || !hash_equals($expected, $token)) {
                throw new HttpException(419, 'Your session expired. Please go back, refresh and try again.');
            }
        }
        return $next($request);
    }
}
