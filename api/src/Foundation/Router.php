<?php

namespace App\Foundation;

final class Router
{
    public array $routes = [];

    public function __construct(array $routes)
    {
        $this->routes = $routes;
        usort($this->routes, function (Route $a, Route $b) {
            return $b->patternLength() - $a->patternLength();
        });
    }

    public function matches(string $method, string $path): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->matches($method, $path)) {
                return $route;
            }
        }
        return null;
    }
}
