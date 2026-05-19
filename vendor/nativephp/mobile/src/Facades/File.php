<?php

namespace Native\Mobile\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool move(string $from, string $to)
 * @method static bool copy(string $from, string $to)
 */
class File extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Native\Mobile\File::class;
    }
}
