<?php

namespace App\Foundation\Container;

interface IComponentRegistration
{
    public function shared(): IComponentRegistration;
    public function arguments(array $arguments): IComponentRegistration;
}
