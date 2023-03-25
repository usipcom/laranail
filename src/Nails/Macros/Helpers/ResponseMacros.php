<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Helpers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Simtabi\Laranail\Nails\Macros\Supports\Response\Error;
use Simtabi\Laranail\Nails\Macros\Supports\Response\Message;
use Simtabi\Laranail\Nails\Macros\Supports\Response\Pdf;
use Simtabi\Laranail\Nails\Macros\Supports\Response\Success;

class ResponseMacros
{
    /**
     * Macros.
     * @var array
     */
    protected $macros = [];

    /**
     * Constructor.
     * @param ResponseFactory $factory
     */
    public function __construct(ResponseFactory $factory)
    {
        $this->macros = [
            Message::class,
            Success::class,
            Error::class,
            Pdf::class,
        ];

        $this->bindMacros($factory);
    }

    /**
     * Bind macros.
     * @param  ResponseFactory $factory
     * @return void
     */
    public function bindMacros($factory)
    {
        foreach ($this->macros as $macro) {
            (new $macro)->run($factory);
        }
    }
}