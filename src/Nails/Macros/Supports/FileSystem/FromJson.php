<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\FileSystem;

use Closure;
use Illuminate\Support\Facades\File;

class FromJson
{
    public function __invoke(): Closure
    {
        return function (string $pathOrContent): ?array
        {
            $content = File::exists($pathOrContent)
                ? File::get($pathOrContent)
                : $pathOrContent;

            return json_decode($content, true);
        };
    }
}
