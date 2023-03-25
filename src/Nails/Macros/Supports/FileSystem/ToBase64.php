<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\FileSystem;

use Closure;
use Illuminate\Support\Facades\File;

class ToBase64
{
    public function __invoke(): Closure
    {
        return function (string $path): string
        {
            $mime    = File::mimeType($path);
            $content = base64_encode(File::get($path));

            return sprintf("data:%s;base64,%s", $mime, $content);
        };
    }
}
