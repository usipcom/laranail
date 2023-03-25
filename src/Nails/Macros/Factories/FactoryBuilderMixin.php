<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Factories;

use Closure;

class FactoryBuilderMixin
{
    /**
     * @return Closure
     */
    public function withoutEvents()
    {
        return function()
        {
            $this->class::flushEventListeners();
  
            return $this;
        };
    }
}