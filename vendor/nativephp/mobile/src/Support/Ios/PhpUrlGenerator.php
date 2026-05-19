<?php

namespace Native\Mobile\Support\Ios;

use Illuminate\Routing\UrlGenerator;

class PhpUrlGenerator extends UrlGenerator
{
    public function formatScheme($secure = null)
    {
        return 'php://';
    }
}
