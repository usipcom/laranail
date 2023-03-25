<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Response;

use Simtabi\Laranail\Nails\Macros\Contracts\ResponseMacroInterface;

class Success implements ResponseMacroInterface
{
    public function run($factory)
    {
        $factory->macro('success', function ($data, $status = 200) use ($factory) {
            return $factory->make([
                'data' => $data,
            ], $status);
        });
    }
}