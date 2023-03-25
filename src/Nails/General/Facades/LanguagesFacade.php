<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\General\Facades;

use Illuminate\Support\Facades\Facade;
use Simtabi\Laranail\Nails\General\Helpers\Locale\Languages;

class LanguagesFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Languages::class;
    }
}
