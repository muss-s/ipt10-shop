<?php

namespace Core;

use Core\Middleware\Middleware;

/**
 * The application kernel: matches the route, runs global + route middleware,
 * calls the controller, and turns errors into proper error pages.
 */
class App
{
    private array $globalMiddleware = [];
    private array $middlewareAliases = [];

    public function __construct(private Router $router, private string $basePath)
    {
    }

    public function router(): Router
    {
        return $this->router;
    }

    /** Middleware that runs on every request. */
    public function use(string $middlewareClass): void
    {
        $this->globalMiddleware[] = $middlewareClass;
    }

    /** Short names for route middleware, e.g. 'auth' => AuthMiddleware::class. */
    public function aliasMiddleware(array $aliases): void
    {
        $this->middlewareAliases = array_merge($this->middlewareAliases, $aliases);
    }

    public function handle(Request $request): Response
    {
        try {
            // The router runs inside the pipeline so global middleware (logging,
            // CSRF) also sees requests that end in 404.
            $core = function (Request $request): Response {
                [$route, $params] = $this->router->match($request->method(), $request->path());
                $request->setParams($params);
                return $this->runPipeline($route->middleware, $request, fn (Request $r) => $this->callAction($route->action, $r));
            };
            return $this->runPipeline($this->globalMiddleware, $request, $core);
        } catch (HttpException $e) {
            return $this->errorResponse($e->status(), $e->getMessage());
        } catch (\Throwable $e) {
            Logger::error($e::class . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            $message = Config::get('app.debug') ? $e->getMessage() . "\n\n" . $e->getTraceAsString() : 'Something went wrong.';
            return $this->errorResponse(500, $message);
        }
    }

    public function run(): void
    {
        $this->handle(Request::capture(parse_url(Config::get('app.url', ''), PHP_URL_PATH) ?? ''))->send();
    }

    /**
     * Wraps $destination in each middleware, outermost first (onion model).
     */
    private function runPipeline(array $middleware, Request $request, callable $destination): Response
    {
        $next = $destination;
        foreach (array_reverse($middleware) as $name) {
            $class = $this->middlewareAliases[$name] ?? $name;
            $next = function (Request $req) use ($class, $next): Response {
                $instance = new $class();
                if (!$instance instanceof Middleware) {
                    throw new \LogicException("$class must implement Middleware");
                }
                return $instance->handle($req, $next);
            };
        }
        return $next($request);
    }

    private function callAction(callable|array $action, Request $request): Response
    {
        if (is_array($action)) {
            [$class, $method] = $action;
            $action = [new $class(), $method];
        }
        $result = $action($request);
        return $result instanceof Response ? $result : Response::html((string) $result);
    }

    private function errorResponse(int $status, string $message): Response
    {
        try {
            return Response::html(View::render('errors/error', ['status' => $status, 'message' => $message]), $status);
        } catch (\Throwable) {
            return Response::html('<h1>' . $status . '</h1><pre>' . e($message) . '</pre>', $status);
        }
    }
}
