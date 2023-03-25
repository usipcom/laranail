<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Blade\Supports\Transformers;

use Simtabi\Laranail\Nails\Blade\Contracts\BladeTransformerInterface;

class StringTransformer implements BladeTransformerInterface
{
    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function canTransform($value): bool
    {
        return is_string($value);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function transform($value): string
    {
        return "'{$this->escape($value)}'";
    }

    protected function escape(string $value): string
    {
        return str_replace(['\\', "'", "\r", "\n", '<', '>'], ['\\\\', "\'", '\\r', '\\n', '\<', '\>'], $value);
    }
}
