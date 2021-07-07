<?php

namespace App\Models;

use App\Foundation\DatabaseConnection;

final class TodoDatabase
{
    private DatabaseConnection $db;

    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    public function save(TodoItem $item): TodoItem
    {
        if ($item->id) {
            return $this->update($item);
        } else {
            return $this->insert($item);
        }
    }

    public function update(TodoItem $item): TodoItem
    {
        $this->db->execute(
            "UPDATE todos SET title = :title, completed = :completed WHERE id = :id",
            [
                "id" => $item->id,
                "title" => $item->title,
                "completed" => (int)$item->completed
            ]
        );
        return $item;
    }

    public function insert(TodoItem $item): TodoItem
    {
        $this->db->execute(
            "INSERT INTO todos(title, completed) VALUES(:title, :completed)",
            [
                "title" => $item->title,
                "completed" => (int)$item->completed
            ]
        );
        $item->id = $this->db->lastInsertId();
        return $item;
    }

    public function removeById(int $id): ?TodoItem
    {
        $item = $this->findById($id);
        if ($item) {
            $this->db->execute("DELETE FROM todos WHERE ID = :id", ["id" => $item->id]);
        }
        return $item;
    }

    public function findById(int $id): ?TodoItem
    {
        $items = $this->db->queryAll("SELECT * FROM todos WHERE ID = :id", ["id" => $id]);
        if (count($items) > 0) {
            return TodoItem::fromArray($items[0]);
        }
        return null;
    }

    public function findAll(): array
    {
        $models = [];
        $items = $this->db->queryAll("SELECT * FROM todos ORDER BY id ASC");

        foreach ($items as $item) {
            $models[] = TodoItem::fromArray($item);
        }

        return $models;
    }
}
