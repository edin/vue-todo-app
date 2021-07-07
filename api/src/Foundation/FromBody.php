<?php

namespace App\Foundation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final class FromBody
{
    public function resolve($typeName)
    {
        return $typeName::fromArray(Request::getInputAsJson());
    }
}
