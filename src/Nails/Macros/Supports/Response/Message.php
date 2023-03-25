<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Response;

use Simtabi\Laranail\Nails\Macros\Contracts\ResponseMacroInterface;

class Message implements ResponseMacroInterface
{
    public function run($factory)
    {
        $factory->macro('message', function ($message, $status) use ($factory) {
            return $factory->make([
                'message' => $message,
            ], $status);
        });
    }
}