<?php

declare(strict_types=1);

namespace StudyFlow\Core;

use StudyFlow\Core\Response;

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][$this->convertToRegex($path)] = $handler;
    }

    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][$this->convertToRegex($path)] = $handler;
    }

    private function convertToRegex(string $path): string
    {
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $regex . '$#';
    }

    public function dispatch(string $method, string $path): void
    {
        $pathExists = false;
        $allowedMethods = [];
        $matchedHandler = null;
        $matchedParams = [];

        foreach ($this->routes as $routeMethod => $regexes) {
            foreach ($regexes as $regex => $handler) {
                if (preg_match($regex, $path, $matches)) {
                    $pathExists = true;
                    $allowedMethods[] = $routeMethod;
                    if ($routeMethod === $method) {
                        $matchedHandler = $handler;
                        $matchedParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    }
                }
            }
        }

        if (!$pathExists) {
            Response::notFound();
            return;
        }

        if ($matchedHandler === null) {
            Response::methodNotAllowed($allowedMethods);
            return;
        }

        [$controllerClass, $action] = explode('@', $matchedHandler);
        
        if (strpos($controllerClass, '\\') !== 0) {
            $controllerClass = '\\StudyFlow\\Controllers\\' . $controllerClass;
        }

        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            if (method_exists($controller, $action)) {
                call_user_func_array([$controller, $action], $matchedParams);
                return;
            }
        }

        Response::notFound('Handler not found');
    }
}
