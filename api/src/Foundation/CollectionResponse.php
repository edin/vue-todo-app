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
        return Response::json(["data" => $this->collection], 200);
    }
}
