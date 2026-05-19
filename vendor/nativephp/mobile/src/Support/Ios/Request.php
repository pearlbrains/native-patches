<?php

namespace Native\Mobile\Support\Ios;

use Illuminate\Http\Request as IlluminateRequest;

class Request extends IlluminateRequest
{
    public function getScheme(): string
    {
        return 'php';
    }

    public function getSchemeAndHttpHost(): string
    {
        return 'php://127.0.0.1';
    }
}
