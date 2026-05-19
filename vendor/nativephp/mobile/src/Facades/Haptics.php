<?php

namespace Native\Mobile\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void vibrate()
 */
class Haptics extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Native\Mobile\Haptics::class;
    }
}
