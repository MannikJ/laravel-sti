<?php

namespace MannikJ\Laravel\SingleTableInheritance\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MannikJ\LaravelSti\Skeleton\SkeletonClass
 */
class STI extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sti';
    }
}
