<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports;

class IfMacro
{
    public function __invoke()
    {
        return function (mixed $if, mixed $then = null, mixed $else = null): mixed {
            return value($if, $this) ? value($then, $this) : value($else, $this);
        };
    }
}
