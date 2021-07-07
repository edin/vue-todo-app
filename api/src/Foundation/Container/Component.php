<?php

namespace App\Foundation\Container;

final class Component implements IComponentRegistration
{
    public string $type;
    public $target;
    public bool $isShared = false;
    public $instance = null;
    public $arguments = [];

    public function __construct(string $type, $target = null)
    {
        $this->type = $type;
        $this->target = $target ?? $type;
    }

    public function asShared(): IComponentRegistration
    {
        $this->isShared = true;
        return $this;
    }

    public function withArguments(array $arguments): IComponentRegistration
    {
        $this->arguments = $arguments;
        return $this;
    }
}
