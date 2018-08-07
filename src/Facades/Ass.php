<?php

namespace Aegisub\Facades;

use Illuminate\Support\Facades\Facade;

class Ass extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'aegisub';
    }
}