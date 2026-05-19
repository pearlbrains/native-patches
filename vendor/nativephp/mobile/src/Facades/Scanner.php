<?php

namespace Native\Mobile\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Native\Mobile\PendingScanner scan()
 * @method static \Native\Mobile\PendingScanner make()
 *
 * @see \Native\Mobile\Scanner
 */
class Scanner extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Native\Mobile\Scanner::class;
    }
}
