<?php

namespace App;

use App\Controllers\TodoController;
use App\Foundation\DatabaseConnection;
use App\Foundation\EntityResponse;
use App\Foundation\Response;
use App\Foundation\Route;
use App\Foundation\Router;
use App\Models\TodoDatabase;

final class Application
{
    public function run()
    {
        $method = $_SERVER["REQUEST_METHOD"];
        $path = $_SERVER["PATH_INFO"] ?? "";
        $queryString = $_SERVER["QUERY_STRING"] ?? "";

        $connection = new DatabaseConnection();
        $todos = new TodoDatabase($connection);
        $controller = new TodoController($todos);

        $router = new Router([
            Route::get("/todos", [$controller, "findAll"]),
            Route::post("/todos", [$controller, "create"]),
            Route::put("/todos/{id:\d+}", [$controller, "update"]),
            Route::delete("/todos/{id:\d+}", [$controller, "remove"]),
        ]);

        $route = $router->matches($method, $path);

        if ($route) {
            $response = call_user_func_array(
                $route->handler,
                $route->params
            );

            if (method_exists($response, "toResponse")) {
                $response = $response->toResponse();
            }

            if ($response instanceof Response) 
            {
                $response->send();
            }

        } else {
            Response::json(["error" => "Invalid route"])
                ->withStatusCode(404)
                ->send();
        }
    }
}
