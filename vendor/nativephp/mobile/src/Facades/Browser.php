<?php

namespace Native\Mobile\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool open(string $url)
 * @method static bool inApp(string $url)
 * @method static bool auth(string $url)
 *
 * @see \Native\Mobile\Browser
 */
class Browser extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Native\Mobile\Browser::class;
    }
}
