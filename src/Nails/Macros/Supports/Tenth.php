<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports;

class Tenth
{
    public function __invoke()
    {
        return function () {
            return $this->skip(9)->first();
        };
    }
}
