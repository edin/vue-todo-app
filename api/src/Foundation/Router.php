<?php

namespace App\Foundation;

final class Router
{
    public array $routes = [];

    public function __construct(array $routes = [])
    {
        $this->routes = $routes;
    }

    public function matches(string $method, string $path): ?Route
    {
        usort($this->routes, function (Route $a, Route $b) {
            return $b->patternLength() - $a->patternLength();
        });
        foreach ($this->routes as $route) {
            if ($route->matches($method, $path)) {
                return $route;
            }
        }
        return null;
    }

    public function add(Route $route)
    {
        $this->routes[] = $route;
    }

    public function addController(string $type)
    {
        $reflection = new \ReflectionClass($type);
        $methods = $reflection->getMethods();
        foreach ($methods as $method) {
            $attributes = $method->getAttributes(Route::class);
            foreach ($attributes as $attribute) {
                $route = $attribute->newInstance();
                $route->setHandler([$type, $method->getName()]);
                $this->routes[] = $route;
            }
        }
    }
}
