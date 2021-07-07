<?php

namespace App;

use App\Controllers\TodoController;
use App\Foundation\Container\Container;
use App\Foundation\Container\IContainer;
use App\Foundation\DatabaseConnection;
use App\Foundation\Dispatcher;
use App\Foundation\IDispatcher;
use App\Foundation\Response;
use App\Foundation\Router;


final class Application
{
    public function run()
    {
        $router = new Router();
        $router->addController(TodoController::class);

        $di = new Container();
        $di->add(IContainer::class, $di);
        $di->add(Router::class, $router);
        $di->add(DatabaseConnection::class)->asShared();
        $di->add(IDispatcher::class, Dispatcher::class)->asShared();

        $di->get(IDispatcher::class)->dispatch()->send();
    }
}

class BooDispatcher
{
    public function dispatch()
    {
        return Response::json(["message" => "Boo !"]);
    }
}
