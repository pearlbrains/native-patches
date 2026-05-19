<?php

namespace Native\Mobile\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string|null get(string $key)
 * @method static bool set(string $key, ?string $value)
 * @method static bool delete(string $key)
 */
class SecureStorage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Native\Mobile\SecureStorage::class;
    }
}
