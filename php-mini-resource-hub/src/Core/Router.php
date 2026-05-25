<?php

declare(strict_types=1);

namespace Core;

use Support\Response;

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        $pathExists = false;
        $allowedMethods = [];
        
        foreach ($this->routes as $routeMethod => $paths) {
            if (isset($paths[$path])) {
                $pathExists = true;
                $allowedMethods[] = $routeMethod;
            }
        }

        if (!$pathExists) {
            Response::notFound();
            return;
        }

        if (!isset($this->routes[$method][$path])) {
            Response::methodNotAllowed($allowedMethods);
            return;
        }

        $handler = $this->routes[$method][$path];
        [$controllerClass, $action] = explode('@', $handler);

        if (strpos($controllerClass, '\\') !== 0) {
            $controllerClass = '\\' . $controllerClass;
        }

        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            if (method_exists($controller, $action)) {
                $controller->$action();
                return;
            }
        }

        Response::notFound('Handler not found');
    }
}
