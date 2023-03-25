<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\FileSystem;

/**
 * Get a file by its path.
 *
 * @param string $path
 *
 * @return \SplFileInfo
 */
class GetFile
{
    public function __invoke(): \Closure
    {
        return function ($path) {
            return new \SplFileInfo($path);
        };
    }
}
