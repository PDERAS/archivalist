<?php

namespace PDERAS\Archivalist;

use Illuminate\Support\Facades\Facade;

/**
 * @see \PDERAS\Archivalist\Skeleton\SkeletonClass
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
        return \PDERAS\Archivalist\Support\Archivalist::class;
    }
}
