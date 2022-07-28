<?php

namespace Lorinczdev\Modely\Facades;

use Illuminate\Support\Facades\Facade;

class Modely extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Lorinczdev\Modely\Modely::class;
    }
}
