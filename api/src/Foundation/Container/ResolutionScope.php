<?php

namespace App\Foundation\Container;

final class ResolutionScope implements IContainer
{
    private Container $container;
    private $instances = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function get(string $name)
    {
        return $this->instances[$name] ??= $this->container->resolve($name, $this);
    }
}
