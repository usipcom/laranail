<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Blade\Supports;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Simtabi\Laranail\Nails\Blade\Supports\Transformers\ArrayTransformer;
use Simtabi\Laranail\Nails\Blade\Supports\Transformers\BooleanTransformer;
use Simtabi\Laranail\Nails\Blade\Supports\Transformers\NullTransformer;
use Simtabi\Laranail\Nails\Blade\Supports\Transformers\NumericTransformer;
use Simtabi\Laranail\Nails\Blade\Supports\Transformers\ObjectTransformer;
use Simtabi\Laranail\Nails\Blade\Supports\Transformers\StringTransformer;
use Simtabi\Laranail\Nails\Blade\Contracts\BladeTransformerInterface;
use Simtabi\Laranail\Nails\Blade\Exceptions\UntransformableException;
class DirectiveRenderer
{
    protected $namespace    = 'window';

    protected $transformers = [
        ArrayTransformer::class,
        BooleanTransformer::class,
        NullTransformer::class,
        NumericTransformer::class,
        ObjectTransformer::class,
        StringTransformer::class,
    ];

    public function __construct(Repository $config)
    {
        $this->namespace = $config->get('blade-javascript.namespace', 'window');
    }

    /**
     * @param array ...$arguments
     *
     * @return string
     *
     * @throws \Throwable
     */
    public function render(...$arguments): string
    {
        $variables = $this->normalizeArguments($arguments);

        return view('bladeJavaScript::index', [
            'javaScript' => $this->buildJavaScriptSyntax($variables),
        ])->render();
    }

    /**
     * @param $arguments
     *
     * @return mixed
     */
    protected function normalizeArguments(array $arguments)
    {
        if (count($arguments) === 2) {
            return [$arguments[0] => $arguments[1]];
        }

        if ($arguments[0] instanceof Arrayable) {
            return $arguments[0]->toArray();
        }

        if (! is_array($arguments[0])) {
            $arguments[0] = [$arguments[0]];
        }

        return $arguments[0];
    }

    public function buildJavaScriptSyntax(array $variables): string
    {
        return collect($variables)
            ->map(function ($value, $key) {
                return $this->buildVariableInitialization($key, $value);
            })
            
            ->reduce(function ($javaScriptSyntax, $variableInitialization) {
                return $javaScriptSyntax.$variableInitialization;
            }, $this->buildNamespaceDeclaration());
    }

    protected function buildNamespaceDeclaration(): string
    {
        if (empty($this->namespace)) {
            return '';
        }

        return "window['{$this->namespace}'] = window['{$this->namespace}'] || {};";
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return string
     * @throws UntransformableException
     */
    protected function buildVariableInitialization(string $key, mixed $value): string
    {
        $variableName = $this->namespace ? "window['{$this->namespace}']['{$key}']" : "window['{$key}']";

        return "{$variableName} = {$this->optimizeValueForJavaScript($value)};";
    }

    /**
     * @param mixed $value
     *
     * @return string
     *
     * @throws UntransformableException
     */
    protected function optimizeValueForJavaScript($value): string
    {
        return $this->getTransformer($value)->transform($value);
    }

    public function getAllTransformers(): Collection
    {
        return collect($this->transformers)->map(function (string $className): BladeTransformerInterface
        {
            return new $className();
        });
    }

    /**
     * @param mixed $value
     *
     * @return BladeTransformerInterface
     *
     * @throws UntransformableException
     */
    public function getTransformer($value): BladeTransformerInterface
    {
        foreach ($this->getAllTransformers() as $transformer)
        {
            if ($transformer->canTransform($value)) {
                return $transformer;
            }
        }

        throw UntransformableException::noTransformerFound($value);
    }
}
