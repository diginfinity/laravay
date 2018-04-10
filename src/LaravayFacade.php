<?php
namespace Laravay;

use Illuminate\Support\Facades\Facade;

class LaravayFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravay';
    }
}