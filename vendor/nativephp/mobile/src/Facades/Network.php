<?php

namespace Native\Mobile\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static object|null status()
 */
class Network extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Native\Mobile\Network::class;
    }
}
