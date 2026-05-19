<?php

namespace Native\Mobile\Facades;

use Illuminate\Support\Facades\Facade;
use Native\Mobile\PendingBiometric;

/**
 * @method static PendingBiometric prompt()
 *
 * @see \Native\Mobile\Biometrics
 */
class Biometrics extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Native\Mobile\Biometrics::class;
    }
}
