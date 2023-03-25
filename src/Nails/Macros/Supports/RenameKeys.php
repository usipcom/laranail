<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports;

use Closure;

class RenameKeys
{
    public function __invoke(): Closure
    {
        return function (array $haystack, array $changes): array
        {
            foreach ($changes as $oldKeyName => $newKeyName)
            {
                $haystack[$newKeyName] = $haystack[$oldKeyName];
                unset($haystack[$oldKeyName]);
            }

            return $haystack;
        };
    }
}