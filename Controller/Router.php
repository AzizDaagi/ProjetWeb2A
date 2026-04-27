<?php

namespace App\Controller;

class Router
{
    private array $routes = [];

    public function get(string $action, array $handler): void
    {
        $this->addRoute('GET', $action, $handler);
    }

    public function post(string $action, array $handler): void
    {
        $this->addRoute('POST', $action, $handler);
    }

    public function match(array $methods, string $action, array $handler): void
    {
        foreach ($methods as $method) {
            $this->addRoute(strtoupper($method), $action, $handler);
        }
    }

    public function dispatch(string $action, string $method): void
    {
        $method = strtoupper($method);
        $handler = $this->routes[$method][$action] ?? null;

        if ($handler === null) {
            http_response_code(404);
            echo 'Route not found.';
            return;
        }

        [$controllerClass, $controllerMethod] = $handler;
        $controller = new $controllerClass();
        $controller->{$controllerMethod}();
    }

    private function addRoute(string $method, string $action, array $handler): void
    {
        $this->routes[$method][$action] = $handler;
    }
}
