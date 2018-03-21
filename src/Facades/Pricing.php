<?php

namespace UniSharp\Pricing\Facades;

use Illuminate\Support\Facades\Facade;

class Pricing extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'pricing';
    }
}