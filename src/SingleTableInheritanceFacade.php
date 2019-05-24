<?php

namespace MannikJ\Laravel\SingleTableInheritance;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MannikJ\LaravelSti\Skeleton\SkeletonClass
 */
class SingleTableInheritanceFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'STI';
    }
}
