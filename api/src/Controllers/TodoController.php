<?php

namespace App\Controllers;

use App\Foundation\CollectionResponse;
use App\Foundation\EntityResponse;
use App\Foundation\Route;
use App\Models\TodoDatabase;
use App\Models\TodoItem;

final class TodoController
{
    private TodoDatabase $todos;

    public function __construct(TodoDatabase $todos)
    {
        $this->todos = $todos;
    }

    #[Route("todos")]
    public function findAll() {
        return new CollectionResponse($this->todos->findAll());
    }

    #[Route("todos", "POST")]
    public function create(#[FromBody] TodoItem $model) {
        $model = $this->todos->save($model);
        return EntityResponse::create($model);
    }

    #[Route("todos/{id:\d+}", "PUT")]
    public function update(?int $id, #[FromBody] TodoItem $model) {
        $model->id = (int)$id;
        $model = $this->todos->save($model);
        return EntityResponse::update($model);
    }

    #[Route("todos/{id:\d+}", "DELETE")]
    public function remove(?int $id) {
        $model = $this->todos->removeById($id);
        return EntityResponse::delete($model);
    }
}
