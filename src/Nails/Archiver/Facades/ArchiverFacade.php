<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Archiver\Facades;

use Illuminate\Support\Facades\Facade;
use Simtabi\Laranail\Nails\Archiver\Archiver;

class ArchiverFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Archiver::class;
    }
}