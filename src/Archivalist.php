<?php

namespace Pderas\Archivalist;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Pderas\Archivalist\Skeleton\SkeletonClass
 */
class Archivalist extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Pderas\Archivalist\Support\Archivalist::class;
    }
}
