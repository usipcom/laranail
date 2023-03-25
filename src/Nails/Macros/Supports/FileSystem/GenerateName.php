<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\FileSystem;

use Closure;
use Illuminate\Support\Str;

class GenerateName
{
    public function __invoke(): Closure
    {
        return function (string $extension): string
        {
            $name      = Str::random(25);
            $extension = trim($extension, '.');

            return $name . '.' . $extension;
        };
    }
}
