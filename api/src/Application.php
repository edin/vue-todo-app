<?php

namespace App;

use App\Controllers\TodoController;
use App\Foundation\DatabaseConnection;
use App\Foundation\Responsable;
use App\Foundation\Response;
use App\Foundation\Route;
use App\Foundation\Router;
use App\Models\TodoDatabase;

// Sice last time, I've done some refactorings, mostly classes are extrated into own files

// New Refactorings:
// - Refactor to use Dependency Injection Container
// - Define routes using attributes


final class Application
{
    public function run()
    {
        // Replace this:
        $connection = new DatabaseConnection();
        $todos = new TodoDatabase($connection);
        $controller = new TodoController($todos);

        // with:
        // $controller = $container->get(TodoController::class);

        // Replace this:
        $router = new Router([
            Route::get("/todos", [$controller, "findAll"]),
            Route::post("/todos", [$controller, "create"]),
            Route::put("/todos/{id:\d+}", [$controller, "update"]),
            Route::delete("/todos/{id:\d+}", [$controller, "remove"]),
        ]);

        // with DI instead of creating instance we can just use type name and create object when it's needed
        // but first we need to create simple DI container

        // $router = new Router([
        //     Route::get("/todos", [TodoController::class, "findAll"]),
        //     Route::post("/todos", [TodoController::class, "create"]),
        //     Route::put("/todos/{id:\d+}", [TodoController::class, "update"]),
        //     Route::delete("/todos/{id:\d+}", [TodoController::class, "remove"]),
        // ]);

        // and finaly with:
        // $router->addController(TodoController::class);

        $this->dispatch($router)->send();
    }


    private function dispatch(Router $router): Response
    {
        $method = $_SERVER["REQUEST_METHOD"];
        $path = $_SERVER["PATH_INFO"] ?? "";
        $queryString = $_SERVER["QUERY_STRING"] ?? "";

        $route = $router->matches($method, $path);

        if ($route === null) {
            return Response::json(["error" => "Invalid route"])->status(404);
        }
        try
        {
            $result = call_user_func_array(
                $route->handler,
                $route->params
            );

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