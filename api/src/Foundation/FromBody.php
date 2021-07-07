<?php

namespace App\Foundation;

use App\Foundation\Container\IContainer;
use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final class FromBody
{
    public function resolve(IContainer $container, $typeName)
    {
        return $typeName::fromArray(Request::getInputAsJson());
    }
}
