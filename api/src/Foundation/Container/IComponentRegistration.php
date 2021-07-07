<?php

namespace App\Foundation\Container;

interface IComponentRegistration
{
    public function asShared(): IComponentRegistration;
    public function withArguments(array $arguments): IComponentRegistration;
}
