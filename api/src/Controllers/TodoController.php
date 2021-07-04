<?php

namespace App\Controllers;

use App\Foundation\CollectionResponse;
use App\Foundation\EntityResponse;
use App\Foundation\Request;
use App\Models\TodoDatabase;
use App\Models\TodoItem;

final class TodoController 
{
    private TodoDatabase $todos;
    
    public function __construct(TodoDatabase $todos)
    {
        $this->todos = $todos;
    }

    private function getModel(): TodoItem {
        return TodoItem::fromArray(Request::getInputAsJson());
    }

    public function findAll() {
        return new CollectionResponse($this->todos->findAll());
    }   
    
    public function create() {
        $model = $this->getModel();
        $model = $this->todos->save($model);
        return EntityResponse::create($model);
    }

    public function update(?int $id) {
        $model = $this->getModel();
        $model->id = (int)$id;
        $model = $this->todos->save($model);
        return EntityResponse::update($model);
    }

    public function remove(?int $id) {
        $model = $this->todos->removeById($id);
        return EntityResponse::delete($model);
    }
}
