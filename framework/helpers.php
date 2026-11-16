<?php

use Core\Config;
use Core\Session;

/** Escape for HTML output. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Absolute app URL for a path, respecting a subfolder base URL. */
function url(string $path = '/'): string
{
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    $base = rtrim(parse_url((string) Config::get('app.url', ''), PHP_URL_PATH) ?? '', '/');
    return $base . '/' . ltrim($path, '/');
}

function config(string $key, mixed $default = null): mixed
{
    return Config::get($key, $default);
}

function csrf_token(): string
{
    if (!Session::get('_token')) {
        Session::set('_token', bin2hex(random_bytes(32)));
    }
    return Session::get('_token');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function old(string $key, mixed $default = ''): mixed
{
    return Session::getFlash('old', [])[$key] ?? $default;
}

function error_for(string $key): ?string
{
    return Session::getFlash('errors', [])[$key] ?? null;
}

function auth_user(): ?array
{
    return Session::get('user');
}

/** Format centavos in the base currency. */
function peso(int $cents): string
{
    return '₱' . number_format($cents / 100, 2);
}
