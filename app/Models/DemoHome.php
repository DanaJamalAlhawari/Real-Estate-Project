<?php

namespace App\Models;

final class DemoHome
{
    public static function all(): array
    {
        return json_decode(file_get_contents(__DIR__.'/demo-homes.json'), true, flags: JSON_THROW_ON_ERROR);
    }
}
