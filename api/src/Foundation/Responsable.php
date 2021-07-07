<?php

namespace App\Foundation;

interface Responsable
{
    public function toResponse(): Response;
}
