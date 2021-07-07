<?php

namespace App;

use App\Foundation\Router;
use App\Foundation\Response;
use App\Foundation\Dispatcher;
use App\Foundation\IDispatcher;
use App\Controllers\TodoController;
use App\Foundation\DatabaseConnection;
use App\Foundation\Container\Container;
use App\Foundation\Container\IContainer;

final class Application
{
    public function run()
    {
        $router = new Router();
        $router->addController(TodoController::class);

        $di = new Container();

        $di->add(Router::class, $router);
        $di->add(IContainer::class, $di);
        $di->add(IDispatcher::class, Dispatcher::class)->shared();

        $di->add(DatabaseConnection::class, shared: true, arguments: [
            'driver'   => 'mysql',
            'host'     => 'localhost',
            'database' => 'todo',
            'user'     => 'root',
            'password' => 'root'
        ]);

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
