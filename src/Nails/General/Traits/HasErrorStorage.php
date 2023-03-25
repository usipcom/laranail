<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\General\Traits;

use Illuminate\Support\Arr;

trait HasErrorStorage
{

    protected array $errors = [];

    protected function setErrors(array|string $errors): static
    {
        $errors = Arr::wrap($errors);

        if (is_array($this->errors) && (count($this->errors) >= 1)) {
            $this->errors = array_merge($this->errors, $errors);
        } else {
            $this->errors = $errors;
        }
        return $this;
    }

    public function getErrors(string|null $key = null): array
    {
        return !empty($key) ? pheg()->arr()->fetch($key, $this->errors) : $this->errors;
    }

}
