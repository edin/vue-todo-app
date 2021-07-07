<?php

namespace App\Foundation;

interface IDispatcher
{
    public function dispatch(): Response;
}
