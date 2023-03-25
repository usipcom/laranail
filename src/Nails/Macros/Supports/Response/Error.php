<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Response;

use Simtabi\Laranail\Nails\Macros\Contracts\ResponseMacroInterface;

class Error implements ResponseMacroInterface
{
    public function run($factory)
    {
        $factory->macro('error', function ($message = 'Bad Request', $status = 400) use ($factory) {
            return $factory->make([
                'errors' => [
                    'message' => $message,
                ],
            ], $status);
        });
    }
}