<?php
namespace App\Core;

/**
 * Router Class
 * Handles URL routing and dispatching
 */
class Router
{
    protected Request $request;
    protected Response $response;
    protected array $routes = [];
    protected array $middlewares = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Register a GET route
     */
    public function get(string $path, $callback, array $middlewares = []): self
    {
        $this->routes['GET'][$path] = [
            'callback' => $callback,
            'middlewares' => $middlewares
        ];
        return $this;
    }

    /**
     * Register a POST route
     */
    public function post(string $path, $callback, array $middlewares = []): self
    {
        $this->routes['POST'][$path] = [
            'callback' => $callback,
            'middlewares' => $middlewares
        ];
        return $this;
    }

    /**
     * Register a PUT route
     */
    public function put(string $path, $callback, array $middlewares = []): self
    {
        $this->routes['PUT'][$path] = [
            'callback' => $callback,
            'middlewares' => $middlewares
        ];
        return $this;
    }

    /**
     * Register a DELETE route
     */
    public function delete(string $path, $callback, array $middlewares = []): self
    {
        $this->routes['DELETE'][$path] = [
            'callback' => $callback,
            'middlewares' => $middlewares
        ];
        return $this;
    }

    /**
     * Add global middleware
     */
    public function addMiddleware(string $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Resolve the current route
     */
    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();
        
        // Handle PUT and DELETE methods via POST with _method field
        if ($method === 'POST') {
            $body = $this->request->getBody();
            if (isset($body['_method'])) {
                $method = strtoupper($body['_method']);
            }
        }
        
        $route = $this->matchRoute($path, $method);
        
        if ($route === false) {
            $this->response->setStatusCode(404);
            return View::render('errors/404');
        }
        
        $callback = $route['callback'];
        $params = $route['params'] ?? [];
        $middlewares = array_merge($this->middlewares, $route['middlewares'] ?? []);
        
        // Execute middlewares
        foreach ($middlewares as $middleware) {
            $middlewareClass = "App\\Middleware\\$middleware";
            if (class_exists($middlewareClass)) {
                $middlewareInstance = new $middlewareClass();
                $result = $middlewareInstance->handle($this->request);
                if ($result !== true) {
                    return $result;
                }
            }
        }
        
        // Execute callback
        if (is_string($callback)) {
            return View::render($callback);
        }
        
        if (is_array($callback)) {
            $controller = new $callback[0]();
            $callback[0] = $controller;
        }
        
        return call_user_func($callback, $this->request, $this->response, $params);
    }

    /**
     * Match route with parameters
     */
    protected function matchRoute(string $path, string $method): array|false
    {
        if (!isset($this->routes[$method])) {
            return false;
        }
        
        foreach ($this->routes[$method] as $route => $routeData) {
            // Convert route parameters to regex
            $pattern = preg_replace('/\{([a-zA-Z]+)\}/', '(?P<$1>[^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $path, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return array_merge($routeData, ['params' => $params]);
            }
        }
        
        return false;
    }
}
