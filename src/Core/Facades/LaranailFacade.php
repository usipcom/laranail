<?php declare(strict_types=1);

namespace Simtabi\Laranail\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Simtabi\Laranail\Core\Laranail;

/**
 * @mixin Laranail
 */
class LaranailFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Laranail::class;
    }
}