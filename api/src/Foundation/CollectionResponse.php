<?php

namespace App\Foundation;

final class CollectionResponse implements Responsable
{
    private $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function toResponse(): Response
    {
        $data = [
            "data" => $this->collection
        ];
        return Response::json($data, 200);
    }
}