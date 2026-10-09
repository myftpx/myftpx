<?php
namespace App\Core;

class Router
{
    protected array $routes = [];

    public function get(string $pattern, $handler): void
    {
        $this->routes['GET'][] = ['pattern' => $pattern, 'handler' => $handler];
    }

    public function post(string $pattern, $handler): void
    {
        $this->routes['POST'][] = ['pattern' => $pattern, 'handler' => $handler];
    }

    public function any(string $pattern, $handler): void
    {
        $this->get($pattern, $handler);
        $this->post($pattern, $handler);
    }

    public function all(string $pattern, $handler): void
    {
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'] as $m) {
            $this->routes[$m][] = ['pattern' => $pattern, 'handler' => $handler];
        }
    }

    public function dispatch(string $method, string $uri)
    {
        $method = strtoupper($method);
        $uri = $this->normalize($uri);
        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route) {
            $params = $this->match($route['pattern'], $uri);
            if ($params !== false) {
                return $this->invoke($route['handler'], $params);
            }
        }
        http_response_code(404);
        View::layout('default');
        echo View::render('errors/404', ['title' => 'Sayfa Bulunamadı']);
        return null;
    }

    protected function normalize(string $uri): string
    {
        $uri = explode('?', $uri)[0];
        $uri = rawurldecode($uri);
        $uri = rtrim($uri, '/');
        if ($uri === '') $uri = '/';
        return $uri;
    }

    protected function match(string $pattern, string $uri): array|false
    {
        // Convert {param} to regex capture, {param?} optional
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\?\}/', '(?P<$1>[^/]*)', $pattern);
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $regex);
        $regex = '#^' . $regex . '$#';
        if (preg_match($regex, $uri, $matches)) {
            $params = [];
            foreach ($matches as $k => $v) {
                if (is_string($k)) $params[$k] = $v;
            }
            return $params;
        }
        return false;
    }

    protected function invoke($handler, array $params)
    {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }
        // [ClassName::class, 'method'] form for non-static methods
        if (is_array($handler) && isset($handler[0], $handler[1]) && is_string($handler[0])) {
            $controller = new $handler[0]();
            return call_user_func_array([$controller, $handler[1]], $params);
        }
        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $controller = new $class();
            return call_user_func_array([$controller, $method], $params);
        }
        throw new \RuntimeException('Invalid route handler');
    }
}
