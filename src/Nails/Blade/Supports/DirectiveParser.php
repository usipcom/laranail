<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Blade\Supports;

use Illuminate\Support\Collection;

class DirectiveParser
{
    /**
     * Parse expression.
     *
     * @param  string  $expression
     * @return Collection
     */
    public static function multipleArgs($expression): Collection
    {
        return collect(explode(',', $expression))->map(function ($item) {
            return trim($item);
        });
    }

    /**
     * Strip quotes.
     *
     * @param  string  $expression
     * @return string
     */
    public static function stripQuotes($expression): string
    {
        return str_replace(["'", '"'], '', $expression);
    }
}