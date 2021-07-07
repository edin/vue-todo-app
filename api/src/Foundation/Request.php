<?php

namespace App\Foundation;

final class Request
{
    public static function getInput()
    {
        return file_get_contents('php://input');
    }

    public static function getInputAsJson()
    {
        return json_decode(self::getInput(), true);
    }
}
