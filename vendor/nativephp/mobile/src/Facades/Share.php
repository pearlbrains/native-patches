<?php

namespace Native\Mobile\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void url(string $title, string $text, string $url)
 * @method static void file(string $title, string $text, string $filePath)
 */
class Share extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Native\Mobile\Share::class;
    }
}
