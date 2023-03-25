<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\FileSystem;

use Closure;

class FromBase64
{
    public function __invoke(): Closure
    {
        return function (string $content): string
        {
            return base64_decode(explode(',', $content)[1]);
        };
    }
}
