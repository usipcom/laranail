<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports;

use Spatie\CollectionMacros\Helpers\CatchableCollectionProxy;

/**
 * @todo revisit for later updates
 */
class TryCatch
{
    public function __invoke()
    {
        return function () {
            return new CatchableCollectionProxy($this);
        };
    }
}
