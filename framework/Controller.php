<?php

namespace Core;

/**
 * Base controller with shortcuts for views, redirects, JSON and validation.
 */
abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/main'): Response
    {
        return Response::html(View::render($view, $data, $layout));
    }

    protected function redirect(string $to): Response
    {
        return Response::redirect($to);
    }

    protected function back(Request $request, string $fallback = '/'): Response
    {
        $ref = $request->server('HTTP_REFERER');
        return $ref ? new Response('', 302, ['Location' => $ref]) : Response::redirect($fallback);
    }

    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    /**
     * Validate input. On failure, flashes errors + old input and returns a
     * redirect Response; on success returns null.
     */
    protected function validate(Request $request, array $rules, string $redirectTo): ?Response
    {
        $validator = new Validator($request->all(), $rules);
        if (!$validator->fails()) {
            return null;
        }
        Session::flash('errors', $validator->errors());
        Session::flash('old', array_diff_key($request->all(), array_flip(['password', 'password_confirmation', '_token'])));
        return Response::redirect($redirectTo);
    }
}
