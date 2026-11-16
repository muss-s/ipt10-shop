<?php

namespace Core;

/**
 * Maps METHOD + URL pattern to a controller action. Patterns may hold {params}.
 *   $router->get('/products/{slug}', [ProductController::class, 'show']);
 *   $router->group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], fn ($r) => ...);
 */
class Router
{
    private array $routes = [];
    private array $groupStack = [];

    public function get(string $path, callable|array $action): Route
    {
        return $this->add('GET', $path, $action);
    }

    public function post(string $path, callable|array $action): Route
    {
        return $this->add('POST', $path, $action);
    }

    public function delete(string $path, callable|array $action): Route
    {
        return $this->add('DELETE', $path, $action);
    }

    public function group(array $options, callable $callback): void
    {
        $this->groupStack[] = $options;
        $callback($this);
        array_pop($this->groupStack);
    }

    public function add(string $method, string $path, callable|array $action): Route
    {
        $prefix = '';
        $middleware = [];
        foreach ($this->groupStack as $group) {
            $prefix .= rtrim($group['prefix'] ?? '', '/');
            $middleware = array_merge($middleware, $group['middleware'] ?? []);
        }

        $route = new Route($method, '/' . trim($prefix . '/' . trim($path, '/'), '/'), $action);
        $route->middleware($middleware);
        $this->routes[] = $route;
        return $route;
    }

    /**
     * Returns [Route, params] for a match, or throws HttpException 404/405.
     */
    public function match(string $method, string $path): array
    {
        $allowed = [];
        foreach ($this->routes as $route) {
            $params = $route->matches($path);
            if ($params === null) {
                continue;
            }
            if ($route->method === $method || ($method === 'HEAD' && $route->method === 'GET')) {
                return [$route, $params];
            }
            $allowed[] = $route->method;
        }

        if ($allowed) {
            throw new HttpException(405, 'Method Not Allowed');
        }
        throw new HttpException(404, 'Page Not Found');
    }

    public function routes(): array
    {
        return $this->routes;
    }
}

class Route
{
    public array $middleware = [];
    private string $regex;

    public function __construct(
        public string $method,
        public string $path,
        public mixed $action,
    ) {
        // /products/{slug} -> #^/products/(?P<slug>[^/]+)$#
        $this->regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path) . '$#';
    }

    public function middleware(array|string $names): self
    {
        $this->middleware = array_merge($this->middleware, (array) $names);
        return $this;
    }

    public function matches(string $path): ?array
    {
        if (!preg_match($this->regex, $path, $m)) {
            return null;
        }
        return array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
    }
}
