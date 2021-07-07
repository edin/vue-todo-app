<?php

namespace App\Foundation;

use App\Foundation\Container\IContainer;
use App\Foundation\Container\ResolutionScope;

final class Dispatcher implements IDispatcher
{
    private IContainer $container;
    private Router $router;

    public function __construct(IContainer $container, Router $router)
    {
        $this->container = $container;
        $this->router = $router;
    }

    public function dispatch(): Response
    {
        $method = $_SERVER["REQUEST_METHOD"];
        $path = $_SERVER["PATH_INFO"] ?? "";
        $queryString = $_SERVER["QUERY_STRING"] ?? "";

        $route = $this->router->matches($method, $path);

        if ($route === null)
        {
            return Response::json(["error" => "Route not found"])->status(404);
        }

        try
        {
            [$typeName, $method] = $route->handler;

            $scope = new ResolutionScope($this->container);

            $instance = $scope->get($typeName);

            $reflection = new \ReflectionClass($instance);
            $method = $reflection->getMethod($method);
            $arguments = [];

            foreach($method->getParameters() as $param)
            {
                $name = $param->getName();
                $type = $param->getType();

                $defaultValue = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
                $arguments[$name] = $route->params[$name] ?? $defaultValue;

                if ($type instanceof \ReflectionNamedType)
                {
                    $attribute = $param->getAttributes(FromBody::class)[0] ?? null;
                    if ($attribute) {
                        $arguments[$name] = $attribute->newInstance()->resolve($scope, $type->getName());
                    }
                    else {
                        $arguments[$name] = $scope->get($type->getName());
                    }
                }
            }

            $result = $method->invokeArgs($instance, $arguments);

            if ($result instanceof Responsable) {
                $result = $result->toResponse();
            }

            if ($result instanceof Response) {
                return $result;
            } elseif (is_array($result) || is_object($result)) {
                return Response::json($result)->status(200);
            } elseif (is_string($result)) {
                $response = new Response();
                $response->body($result);
                return $response;
            } else {
                $type = gettype($result);
                throw new \RuntimeException("Can't convert result of type {$type} to valid Response");
            }
        } catch (\Exception $e) {
            return Response::json(["error" => $e->getMessage()])->status(500);
        }
    }
}