<?php

namespace App\Foundation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class RouteAttribute
{
    public array $methods;
    public string $pattern;

    public function __construct($methods, string $pattern)
    {
        $this->methods = (array)$methods;
        $this->pattern = $pattern;
    }
}