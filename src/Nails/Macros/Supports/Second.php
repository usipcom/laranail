<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports;

class Second
{
    public function __invoke()
    {
        return function () {
            return $this->skip(1)->first();
        };
    }
}
