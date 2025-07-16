<?php

namespace Asciisd\Cybersource\Facades;

use Illuminate\Support\Facades\Facade;

class Cybersource extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cybersource';
    }
} 