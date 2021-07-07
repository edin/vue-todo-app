<?php

namespace App\Foundation\Container;

interface IContainer
{
    public function get(string $name);
}
