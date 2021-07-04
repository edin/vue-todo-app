<?php

namespace App\Models;

final class TodoItem 
{
    public ?int $id = null;
    public string $title = "";
    public bool $completed = false;

    public function __construct(
        ?int $id,
        string $title,
        bool $completed = false)
    {
        $this->id = $id;
        $this->title = $title;
        $this->completed = $completed;
    }

    public static function fromArray(array $data): TodoItem {
        return new TodoItem(
            $data['id'] ?? null,
            $data['title'],
            (bool)$data['completed']
        );
    }
}
