<?php

namespace App\Foundation;

final class EntityResponse implements Responsable
{
    private $entity;
    private $status;

    public function __construct($entity, $status = 200)
    {
        $this->entity = $entity;
        $this->status = $status;
    }

    public static function create($entity): EntityResponse
    {
        return new EntityResponse($entity, 201);
    }

    public static function update($entity): EntityResponse
    {
        return new EntityResponse($entity);
    }

    public static function delete($entity): EntityResponse
    {
        return new EntityResponse($entity, 204);
    }

    public function toResponse(): Response
    {
        if ($this->entity) {
            return Response::json(json_encode($this->entity), 200);
        } else {
            return Response::json(["error" => "Entity not found"], 404);
        }
    }
}