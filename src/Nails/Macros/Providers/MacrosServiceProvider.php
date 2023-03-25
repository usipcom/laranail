<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Providers;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Simtabi\Laranail\Nails\Macros\Commands\Macros;
use Simtabi\Laranail\Nails\Macros\Helpers\ResponseMacros;
use Simtabi\Laranail\Nails\Macros\Factories\FactoryBuilderMixin;
use Simtabi\Laranail\Nails\Macros\Supports\Holidays\BrazilianHolidays;
use Simtabi\Laranail\Nails\Macros\Supports\Holidays\CanadianDates;
use Simtabi\Laranail\Nails\Macros\Supports\Holidays\DutchHolidays;
use Simtabi\Laranail\Nails\Macros\Supports\Holidays\FrenchHolidays;
use Simtabi\Laranail\Nails\Macros\Supports\Holidays\GermanHolidays;
use Simtabi\Laranail\Nails\Macros\Supports\Holidays\IndianHolidays;
use Simtabi\Laranail\Nails\Macros\Supports\Holidays\IndonesianHolidays;
use Simtabi\Laranail\Nails\Macros\Supports\Holidays\ItalianHolidays;
use Simtabi\Laranail\Nails\Macros\Supports\Holidays\KenyanHolidays;
use Simtabi\Laranail\Nails\Macros\Supports\Holidays\MultiNationalDates;
use Simtabi\Laranail\Nails\Macros\Supports\Holidays\SwedishHolidays;
use Simtabi\Laranail\Nails\Macros\Supports\Holidays\UkrainianHolidays;
use Simtabi\Laranail\Nails\Macros\Supports\Holidays\UsDates;
use Simtabi\Laranail\Nails\Macros\Supports\Holidays\ZambianHolidays;
use InvalidArgumentException;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Arr;
use Illuminate\Database\Schema\Blueprint;
use Doctrine\DBAL\Schema\Index;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Stringable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;

class MacrosServiceProvider extends ServiceProvider
{
    use MultiNationalDates;

    use BrazilianHolidays;
    use CanadianDates;
    use FrenchHolidays;
    use GermanHolidays;
    use IndianHolidays;
    use IndonesianHolidays;
    use ItalianHolidays;
    use KenyanHolidays;
    use SwedishHolidays;
    use DutchHolidays;
    use UkrainianHolidays;
    use UsDates;
    use ZambianHolidays;

    /**
     * Register any application services.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function register()
    {
        $this->app->make(ResponseMacros::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConsoles();
        $this->loadCollectionMacros();
        $this->loadStrMacros();
        $this->loadCarbonMacros();
        $this->loadQueryBuilderMacros();
        $this->loadDbBlueprintMacros();
        $this->loadShovelMacros();
        $this->loadArrMacros();
        $this->loadResponseMacros();
        $this->loadRequestMacros();
        $this->loadTestResponse();
        $this->loadMixins();
    }

    private function registerConsoles(): static
    {
        if ($this->app->runningInConsole())
        {
            $this->commands([
                Macros::class,
            ]);
        }

        return $this;
    }

    private function loadCollectionMacros()
    {

        Collection::make([

            /*
             * Get the collection of items as a hierarchical array.
             *
             * @param  string  $foreign
             * @param  string  $primary
             * @param  mixed  $value
             * @return \Illuminate\Support\Collection
             */
            'toHierarchy' => function ($foreign = 'parent_id', $primary = 'id', $value = null) {
                return $this->where($foreign, $value)
                    ->map(function ($parent) use ($foreign, $primary) {
                        return data_set($parent, 'children', $this->toHierarchy(
                            $foreign, $primary, data_get($parent, $primary)
                        ));
                    })
                    ->values();
            },

            /**
             * Paginate a standard Laravel Collection.
             *
             * @param int $perPage
             * @param int $total
             * @param int $page
             * @param string $pageName
             * @return array
             */
            'paginate' => function($perPage, $total = null, $page = null, $pageName = 'page') {
                $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

                return new LengthAwarePaginator($this->forPage($page, $perPage), $total ?: $this->count(), $perPage, $page,
                    [
                        'path' => LengthAwarePaginator::resolveCurrentPath(),
                        'pageName' => $pageName,
                    ]
                );
            },

            /**
             * Create Carbon instances from items in a collection.
             */
            'carbonize' => function () {
                return collect($this->items)->map(function ($time) {
                    return new Carbon($time);
                });
            },

            /**
             * Reduce each collection item to the value found between a given start and end string.
             */
            'between' => function ($start, $end = null) {
                $end = $end ?? $start;

                return collect($this->items)->reduce(function ($items, $value) use ($start, $end) {
                    if (preg_match('/^' . $start . '(.*)' . $end . '$/', $value, $matches)) {
                        $items[] = $matches[1];
                    }

                    return collect($items);
                });
            },

            /**
             * Perform an operation on the collection's keys.
             */
            'transformKeys' => function (callable $operation) {
                return collect($this->items)->mapWithKeys(function ($item, $key) use ($operation) {
                    return [$operation($key) => $item];
                });
            },

            /*
             * Transpose (flip) a collection matrix (array of arrays).
             *
             * @see https://adamwathan.me/2016/04/06/cleaning-up-form-input-with-transpose/
             */
            'transpose' => function () {
                if ($this->isEmpty()) {
                    return $this;
                }

                $items = array_map(function (...$items) {
                    return $items;
                }, ...$this->values());

                return new static($items);
            },

            /*
             * Transpose (flip) a collection matrix (array of arrays) while keeping its columns and row headers intact.
             *
             * Please note that a row missing a column another row does have can only occur for one column. It cannot
             * parse more than one missing column.
             */
            'transposeWithKeys' => function (?array $rows = null) {
                if ($this->isEmpty()) {
                    return $this;
                }

                if ($rows === null) {
                    $rows = $this->values()->reduce(function (array $rows, array $values) {
                        return array_unique(array_merge($rows, array_keys($values)));
                    }, []);
                }

                $keys = $this->keys()->toArray();

                // Transpose the matrix
                $items = array_map(function (...$items) use ($keys) {
                    // The collection's keys now become column headers
                    return array_combine($keys, $items);
                }, ...$this->values());

                // Add the new row headers
                $items = array_combine($rows, $items);

                return new static($items);
            },

            'd' => function () {
                d($this);

                return $this;
            },

            'ddd' => function () {
                ddd($this);
            },

            'diffBy' => function ($items, callable $compareFn) {
                return new static(array_udiff($this->items, $this->getArrayableItems($items), $compareFn));
            },

            'diffAssocBy' => function ($items, callable $valueCompareFunc, callable $keyCompareFunc = null) {
                if ($keyCompareFunc) {
                    return new static(array_udiff_uassoc($this->items, $this->getArrayableItems($items), $valueCompareFunc, $keyCompareFunc));
                }
                else{
                    return new static(array_udiff_assoc($this->items, $this->getArrayableItems($items), $valueCompareFunc));
                }
            },

            'sortCallback' => function (callable $compareFn, $keepKeys = false, $desc = false) {
                $items = $this->items;

                if ($desc) {
                    $comparator = function ($a, $b) use ($compareFn) {
                        return -1 * call_user_func($compareFn, $a, $b);
                    };
                }
                else {
                    $comparator = $compareFn;
                }

                // sort
                $keepKeys ? uasort($items, $comparator) : usort($items, $comparator);

                return new static($items);
            },

            'sortCallbackDesc' => function (callable $compareFn, $keepKeys = false) {
                return $this->sortCallback($compareFn, $keepKeys, true);
            },

            'asKeys' => function($value) {
                return new static(array_fill_keys($this->items, $value));
            },
        ])
            ->reject(fn ($function, $macro) => Collection::hasMacro($macro))
            ->each(fn ($function, $macro)   => Collection::macro($macro, $function));

        Collection::make([
            'increment'              => \Simtabi\Laranail\Nails\Macros\Supports\Increment::class,
            'decrement'              => \Simtabi\Laranail\Nails\Macros\Supports\Decrement::class,
            'krsort'                 => \Simtabi\Laranail\Nails\Macros\Supports\Krsort::class,
            'ksort'                  => \Simtabi\Laranail\Nails\Macros\Supports\Ksort::class,
            'rsort'                  => \Simtabi\Laranail\Nails\Macros\Supports\Rsort::class,
            'replaceInKeys'          => \Simtabi\Laranail\Nails\Macros\Supports\ReplaceInKeys::class,
            'renameKeys'             => \Simtabi\Laranail\Nails\Macros\Supports\RenameKeys::class,
            'fromBase64'             => \Simtabi\Laranail\Nails\Macros\Supports\FileSystem\FromBase64::class,
            'fromJson'               => \Simtabi\Laranail\Nails\Macros\Supports\FileSystem\FromJson::class,
            'generateName'           => \Simtabi\Laranail\Nails\Macros\Supports\FileSystem\GenerateName::class,
            'toBase64'               => \Simtabi\Laranail\Nails\Macros\Supports\FileSystem\ToBase64::class,
            'round5'                 => \Simtabi\Laranail\Nails\Macros\Supports\Math\Round5::class,
            'whenEquals'             => \Simtabi\Laranail\Nails\Macros\Supports\Request\WhenEquals::class,
            'isEquals'               => \Simtabi\Laranail\Nails\Macros\Supports\Request\IsEquals::class,
            'distanceBetween'        => \Simtabi\Laranail\Nails\Macros\Supports\Math\DistanceBetween::class,
            'whereStartsWith'        => \Simtabi\Laranail\Nails\Macros\Supports\Collection\WhereStartsWith::class,
            'getFile'                => \Simtabi\Laranail\Nails\Macros\Supports\FileSystem\GetFile::class,
            'whereEndsWith'          => \Simtabi\Laranail\Nails\Macros\Supports\Collection\WhereEndsWith::class,
            'whereContains'          => \Simtabi\Laranail\Nails\Macros\Supports\Collection\WhereContains::class,
            'forSelectBox'           => \Simtabi\Laranail\Nails\Macros\Supports\Collection\ForSelectBox::class,
            'after'                  => \Simtabi\Laranail\Nails\Macros\Supports\After::class,
            'at'                     => \Simtabi\Laranail\Nails\Macros\Supports\At::class,
            'before'                 => \Simtabi\Laranail\Nails\Macros\Supports\Before::class,
            'chunkBy'                => \Simtabi\Laranail\Nails\Macros\Supports\ChunkBy::class,
            'collectBy'              => \Simtabi\Laranail\Nails\Macros\Supports\CollectBy::class,
            'eachCons'               => \Simtabi\Laranail\Nails\Macros\Supports\EachCons::class,
            'eighth'                 => \Simtabi\Laranail\Nails\Macros\Supports\Eighth::class,
            'extract'                => \Simtabi\Laranail\Nails\Macros\Supports\Extract::class,
            'fifth'                  => \Simtabi\Laranail\Nails\Macros\Supports\Fifth::class,
            'filterMap'              => \Simtabi\Laranail\Nails\Macros\Supports\FilterMap::class,
            'firstOrFail'            => \Simtabi\Laranail\Nails\Macros\Supports\FirstOrFail::class,
            'firstOrPush'            => \Simtabi\Laranail\Nails\Macros\Supports\FirstOrPush::class,
            'fourth'                 => \Simtabi\Laranail\Nails\Macros\Supports\Fourth::class,
            'fromPairs'              => \Simtabi\Laranail\Nails\Macros\Supports\FromPairs::class,
            'getNth'                 => \Simtabi\Laranail\Nails\Macros\Supports\GetNth::class,
            'glob'                   => \Simtabi\Laranail\Nails\Macros\Supports\Glob::class,
            'groupByModel'           => \Simtabi\Laranail\Nails\Macros\Supports\GroupByModel::class,
            'head'                   => \Simtabi\Laranail\Nails\Macros\Supports\Head::class,
            'if'                     => \Simtabi\Laranail\Nails\Macros\Supports\IfMacro::class,
            'ifAny'                  => \Simtabi\Laranail\Nails\Macros\Supports\IfAny::class,
            'ifEmpty'                => \Simtabi\Laranail\Nails\Macros\Supports\IfEmpty::class,
            'insertAfter'            => \Simtabi\Laranail\Nails\Macros\Supports\InsertAfter::class,
            'insertAfterKey'         => \Simtabi\Laranail\Nails\Macros\Supports\InsertAfterKey::class,
            'insertAt'               => \Simtabi\Laranail\Nails\Macros\Supports\InsertAt::class,
            'insertBefore'           => \Simtabi\Laranail\Nails\Macros\Supports\InsertBefore::class,
            'insertBeforeKey'        => \Simtabi\Laranail\Nails\Macros\Supports\InsertBeforeKey::class,
            'ninth'                  => \Simtabi\Laranail\Nails\Macros\Supports\Ninth::class,
            'none'                   => \Simtabi\Laranail\Nails\Macros\Supports\None::class,
            'paginate'               => \Simtabi\Laranail\Nails\Macros\Supports\Paginate::class,
            'parallelMap'            => \Simtabi\Laranail\Nails\Macros\Supports\ParallelMap::class,
            'path'                   => \Simtabi\Laranail\Nails\Macros\Supports\Path::class,
            'pluckMany'              => \Simtabi\Laranail\Nails\Macros\Supports\PluckMany::class,
            'pluckToArray'           => \Simtabi\Laranail\Nails\Macros\Supports\PluckToArray::class,
            'prioritize'             => \Simtabi\Laranail\Nails\Macros\Supports\Prioritize::class,
            'recursive'              => \Simtabi\Laranail\Nails\Macros\Supports\Recursive::class,
            'rotate'                 => \Simtabi\Laranail\Nails\Macros\Supports\Rotate::class,
            'second'                 => \Simtabi\Laranail\Nails\Macros\Supports\Second::class,
            'sectionBy'              => \Simtabi\Laranail\Nails\Macros\Supports\SectionBy::class,
            'seventh'                => \Simtabi\Laranail\Nails\Macros\Supports\Seventh::class,
            'simplePaginate'         => \Simtabi\Laranail\Nails\Macros\Supports\SimplePaginate::class,
            'sixth'                  => \Simtabi\Laranail\Nails\Macros\Supports\Sixth::class,
            'sliceBefore'            => \Simtabi\Laranail\Nails\Macros\Supports\SliceBefore::class,
            'tail'                   => \Simtabi\Laranail\Nails\Macros\Supports\Tail::class,
            'tenth'                  => \Simtabi\Laranail\Nails\Macros\Supports\Tenth::class,
            'third'                  => \Simtabi\Laranail\Nails\Macros\Supports\Third::class,
            'toPairs'                => \Simtabi\Laranail\Nails\Macros\Supports\ToPairs::class,
            'transpose'              => \Simtabi\Laranail\Nails\Macros\Supports\Transpose::class,
            'try'                    => \Simtabi\Laranail\Nails\Macros\Supports\TryCatch::class,
            'validate'               => \Simtabi\Laranail\Nails\Macros\Supports\Validate::class,
            'withSize'               => \Simtabi\Laranail\Nails\Macros\Supports\WithSize::class,
            'paginateFirstDifferent' => \Simtabi\Laranail\Nails\Macros\Supports\Paginators\CollectionMacros\PaginateFirstDifferent::class,
            'paginateWithPrevious'   => \Simtabi\Laranail\Nails\Macros\Supports\Paginators\CollectionMacros\PaginateWithPrevious::class,
        ])
            ->reject(fn ($class, $macro) => Collection::hasMacro($macro))
            ->each(fn ($class, $macro)   => Collection::macro($macro, app($class)()));

    }

    private function loadStrMacros()
    {

        Collection::make([

            'snakeToTitle' => function($value, $replace = '-') {
                return Str::title(str_replace($replace, ' ', $value));
            },

            'truncate' => function (int $length, string $text) {
                if ($length >= strlen($text))
                {
                    return $text;
                }

                return preg_replace("/^(.{1,{$length}})(\\s.*|$)/s", '\\1...', $text);
            },

            'formatAmount' => function ($amount, int $decimal) {
                return number_format($amount, $decimal, '.', '');
            },

            'shuffle' => function(?string $string) : ?string
            {
                return $string ? str_shuffle($string) : null;
            },

            'reverse' => function(?string $string) : ?string
            {
                return $string ? strrev($string) : null;
            },

            /**
             * Calculate the similarity between two strings
             *
             * @param   string|null     $a
             * @param   string|null     $b
             * @param   bool            $caseSensative
             * @param   bool            $smg
             * @return  float
             */
            'similarText' => function(?string $a, ?string $b, bool $caseSensitive = false, bool $smg = true) : float
            {
                if( !$a || !$b ) return 0;
                $comparison = pheg()->str()->compare();
                if( $caseSensitive ) {
                    return max([
                        $comparison->similarText($a,$b)/100,
                        $comparison->jaroWinkler($a,$b),
                        $smg ? $comparison->smg($a,$b) : 0,
                        $comparison->similarText($b,$a)/100,
                        $comparison->jaroWinkler($b,$a),
                        $smg ? $comparison->smg($b,$a) : 0,
                    ]);
                } else {
                    return max([
                            $comparison->similarText($a,$b)/100,
                            $comparison->jaroWinkler($a,$b),
                            $smg ? $comparison->smg($a,$b) : 0,
                            $comparison->similarText($b,$a)/100,
                            $comparison->jaroWinkler($b,$a),
                            $smg ? $comparison->smg($b,$a) : 0,
                            $comparison->similarText(strtoupper($a),strtoupper($b))/100,
                            $comparison->jaroWinkler(strtoupper($a),strtoupper($b)),
                            $smg ? $comparison->smg(strtoupper($a),strtoupper($b)) : 0,
                            $comparison->similarText(strtoupper($b),strtoupper($a))/100,
                            $comparison->jaroWinkler(strtoupper($b),strtoupper($a)),
                            $smg ? $comparison->smg(strtoupper($b),strtoupper($a)) : 0,
                            $comparison->similarText(strtolower($a),strtolower($b))/100,
                            $comparison->jaroWinkler(strtolower($a),strtolower($b)),
                            $smg ? $comparison->smg(strtolower($a),strtolower($b)) : 0,
                            $comparison->similarText(strtolower($b),strtolower($a))/100,
                            $comparison->jaroWinkler(strtolower($b),strtolower($a)),
                            $smg ? $comparison->smg(strtolower($b),strtolower($a)) : 0,
                        ])*100;
                }
            },

            /**
             * Generates a UUID (version 5)
             *
             * Requires package ramsey/uuid
             * @see https://github.com/ramsey/uuid
             *
             * @param   string|null     $name
             * @param   string|null     $namespace
             * @return  string
             */
            'uuid5' => function(?string $name, ?string $namespace = null) : string
            {
                return \Ramsey\Uuid\Uuid::uuid5( $namespace ?: \Ramsey\Uuid\Uuid::NAMESPACE_DNS, $name )->toString();
            },

            /**
             * Extended version of Str::random()
             *
             * @param   int|null        $length
             * @param   string          $characters
             * @return  string
             */
            'randomExt' => function(?int $length = 16, string $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789') : ?string
            {
                if( $length <= 0 || !strlen($characters) ) return null;
                return substr( str_shuffle( implode( '', array_fill( 0, $length, $characters ) ) ), 0, $length );
            },

            'generatePassword' => function(?int $length = 8, string $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!"$%&\'()*+,-./:;<=>?@[\]^_`{|}~', bool $removeAmbiguous = false) : ?string
            {
                if( $removeAmbiguous ) {
                    $characters = str_replace(str_split('B8G6I1l|0OQDS5$Z2()[]{}:;,.\'"`!$-~£¢§'), '', $characters);
                }
                if( $length <= 0 || !strlen($characters) ) return null;
                return substr( str_shuffle( implode( '', array_fill( 0, $length, $characters ) ) ), 0, $length );
            },

            'extract' => function ($haystack, $delimiter, $length, $defaultValue = null, $appendOverflowing = false) {
                $absLength = abs($length);

                // explode string
                if ($appendOverflowing)
                    $sp = explode($delimiter, $haystack, $absLength);
                else
                    $sp = explode($delimiter, $haystack);

                $numParts = count($sp);

                if ($numParts == $absLength) {
                    return $sp;
                }
                elseif ($numParts > $absLength) {
                    return array_slice($sp, 0, $absLength);
                }
                elseif ($length > 0) {
                    // pad right
                    return array_pad($sp, $absLength, $defaultValue);
                }
                else {
                    // pad left
                    return array_merge(array_fill(0, $absLength - $numParts, $defaultValue), $sp);
                }
            },

            'replaceLineBreaks' => function (?string $subject, ?string $replace = ' ') {

                if ($subject === null) {
                    return null;
                }

                return str_replace(["\r\n", "\r", "\n"], $replace, $subject);

            },

            'cutEncoding' => function (?string $string, int $maxBytes, string $targetEncoding): ?string {
                if ($string === null)
                {
                    return null;
                }

                $appEncoding = mb_internal_encoding();

                if ($targetEncoding !== $appEncoding)
                {
                    $string = mb_convert_encoding($string, $targetEncoding, $appEncoding);
                }

                $string = mb_strcut($string, 0, $maxBytes, $targetEncoding);

                // convert our truncated string back to application charset
                if ($targetEncoding !== $appEncoding)
                    $string = mb_convert_encoding($string, $appEncoding, $targetEncoding);

                return $string;
            },

            'isEmpty' => function (?string $value): bool {
                return trim($value) === '' || empty($value);
            },

            'isNotEmpty' => function (?string $value): bool {
                return trim($value) !== '' || trim($value) !== null;
            },

            'ifNotEmpty' => function (?string $value, string $else = null): ?string {

                return trim($value) !== '' ? $value : $else;
            },

            'coalesce' => function (?string ...$values): ?string {

                $curr = null;

                foreach ($values as $curr)
                {
                    if (trim($curr) !== '')
                    {
                        return $curr;
                    }
                }

                return $curr;
            },

            'ucFirstWords' => function (?string $value, bool $forceLower = false, int $forceLowerMinLength = 0, string $splitByRegex = '[^\p{L}]+'): ?string {

                if ($value === null)
                    return null;

                $wordData = preg_split('/(' . $splitByRegex . ')/u', $value, -1, PREG_SPLIT_OFFSET_CAPTURE);

                $segments = [];
                $lastPos  = 0;
                foreach ($wordData as $curr) {

                    // add chars before current word
                    $segments[] = substr($value, $lastPos, $curr[1] - $lastPos);

                    // extract first char and rest
                    $firstChar = Str::substr($curr[0], 0, 1);
                    $rest      = Str::substr($curr[0], 1);

                    // convert to upper/lower case
                    $segments[] = Str::upper($firstChar) . ($forceLower && Str::length($curr[0]) >= $forceLowerMinLength ?
                            Str::lower($rest) :
                            $rest
                        );

                    // set last pos behind current word
                    $lastPos = $curr[1] + strlen($curr[0]);
                }

                $segments[] = substr($value, $lastPos);

                return implode('', $segments);
            },

            'repairInvalidUnicodeSequences' => function (?string $str, string $replacement = "\u{FFFD}"): ?string {
                if ($str === null)
                    return null;

                $ret = htmlspecialchars($str, ENT_DISALLOWED | ENT_SUBSTITUTE);

                if ($replacement !== "\u{FFFD}")
                    $ret = str_replace("\u{FFFD}", $replacement, $ret);

                return htmlspecialchars_decode($ret);
            },

            'limitMax' => function (?string $value, int $limit = 100, string $end = '...'): ?string {

                if ($value === null)
                {
                    return null;
                }

                if (mb_strwidth($value, 'UTF-8') <= $limit)
                {
                    return $value;
                }

                return mb_strimwidth($value, 0, $limit - mb_strwidth($end, 'UTF-8'), '', 'UTF-8') . $end;
            },

            'cast' => function ($value) {

                if (is_iterable($value)) {
                    $ret = [];

                    foreach ($value as $item)
                    {
                        $ret[] = (string) $item;
                    }

                    return $ret;
                }
                else {
                    $value = (string) $value;
                }

                return $value;
            },
        ])
            ->reject(fn ($function, $macro) => Str::hasMacro($macro))
            ->each(fn ($function, $macro)   => Str::macro($macro, $function));

        Collection::make([
            'bind'            => \Simtabi\Laranail\Nails\Macros\Supports\Str\Bind::class,
            'capitalizeWords' => \Simtabi\Laranail\Nails\Macros\Supports\Str\CapitalizeWords::class,
            'highlightWords'  => \Simtabi\Laranail\Nails\Macros\Supports\Str\HighlightWords::class,
            'human'           => \Simtabi\Laranail\Nails\Macros\Supports\Str\Human::class,
            'initials'        => \Simtabi\Laranail\Nails\Macros\Supports\Str\Initials::class,
            'interpolate'     => \Simtabi\Laranail\Nails\Macros\Supports\Str\Interpolate::class,
            'linesCount'      => \Simtabi\Laranail\Nails\Macros\Supports\Str\LinesCount::class,
            'matches'         => \Simtabi\Laranail\Nails\Macros\Supports\Str\Matches::class,
            'readingMinutes'  => \Simtabi\Laranail\Nails\Macros\Supports\Str\ReadingMinutes::class,
            'stripTags'       => \Simtabi\Laranail\Nails\Macros\Supports\Str\StripTags::class,
        ])
            ->reject(fn ($class, $macro) => Str::hasMacro($macro))
            ->each(fn ($class, $macro)   => Str::macro($macro, app($class)()));

        Collection::make([
            'bind'            => \Simtabi\Laranail\Nails\Macros\Supports\Str\Stringable\Bind::class,
            'capitalizeWords' => \Simtabi\Laranail\Nails\Macros\Supports\Str\Stringable\CapitalizeWords::class,
            'highlightWords'  => \Simtabi\Laranail\Nails\Macros\Supports\Str\Stringable\HighlightWords::class,
            'human'           => \Simtabi\Laranail\Nails\Macros\Supports\Str\Stringable\Human::class,
            'initials'        => \Simtabi\Laranail\Nails\Macros\Supports\Str\Stringable\Initials::class,
            'interpolate'     => \Simtabi\Laranail\Nails\Macros\Supports\Str\Stringable\Interpolate::class,
            'linesCount'      => \Simtabi\Laranail\Nails\Macros\Supports\Str\Stringable\LinesCount::class,
            'matches'         => \Simtabi\Laranail\Nails\Macros\Supports\Str\Stringable\Matches::class,
            'readingMinutes'  => \Simtabi\Laranail\Nails\Macros\Supports\Str\Stringable\ReadingMinutes::class,
            'stripTags'       => \Simtabi\Laranail\Nails\Macros\Supports\Str\Stringable\StripTags::class,
        ])
            ->reject(fn ($class, $macro) => Stringable::hasMacro($macro))
            ->each(fn ($class, $macro)   => Stringable::macro($macro, app($class)()));
    }

    private function loadCarbonMacros()
    {
        $this->registerMultinationalDates();

        $this->registerBrazilianHolidays();

        $this->registerCanadianDates();

        $this->registerDutchHolidays();

        $this->registerFrenchHolidays();

        $this->registerGermanHolidays();

        $this->registerIndianHolidays();

        $this->registerIndonesianHolidays();

        $this->registerItalianHolidays();

        $this->registerKenyanHolidays();

        $this->registerSwedishHolidays();

        $this->registerUsDates();

        $this->registerUkrainianHolidays();

        $this->registerZambianHolidays();
    }

    private function loadQueryBuilderMacros()
    {


        Collection::make([

            'toRawSql' => function() {
                return array_reduce($this->getBindings(), function($sql, $binding) {
                    return preg_replace('/\?/', is_numeric($binding) ? $binding : "'".$binding."'" , $sql, 1);
                }, $this->toSql());
            },

            'log' => function() {
                logger($this->toRawSql());

                return $this;
            },

            'relation' => function (string $type = 'max') {
                if(! $where = $this->wheres[0] ?? null) {
                    throw new InvalidArgumentException('The relation methods should only be called from within a whereHas callback.');
                }

                return $this->where('id', function ($sub) use ($where, $type) {
                    $sub->from($this->from)
                        ->selectRaw($type . '(id)')
                        ->whereColumn($where['first'], $where['second']);
                });
            },

            'earliestRelation' => function () {
                return $this->relation('min');
            },

            'latestRelation' => function () {
                return $this->relation('max');
            },

            'whereEarliest' => function ($column, $operator = null, $value = null) {
                return $this->earliestRelation()->where($column, $operator, $value);
            },

            'whereLatest' => function ($column, $operator = null, $value = null) {
                return $this->latestRelation()->where($column, $operator, $value);
            },

        ])
            ->reject(fn ($function, $macro) => QueryBuilder::hasMacro($macro))
            ->each(fn ($function, $macro)   => QueryBuilder::macro($macro, $function));

        Collection::make([

            'toRawSql' => function() {
                return ($this->getQuery()->toRawSql());
            },

            'log' => function() {
                logger($this->toRawSql());

                return $this;
            },

            'whereEarliestRelation' => function ($relation, $column, $operator = null, $value = null) {
                return $this->whereHas($relation, function($query) use ($column, $operator, $value) {
                    return $query->whereEarliest($column, $operator, $value);
                });
            },

            'whereLatestRelation' => function ($relation, $column,  $operator = null, $value = null) {
                return $this->whereHas($relation, function ($query) use ($column, $operator, $value) {
                    $query->whereLatest($column, $operator, $value);
                });
            },

            'addSubSelect' => function ($column, $query) {
                $this->defaultSelectAll();

                return $this->selectSub($query->limit(1)->getQuery(), $column);
            },

            'defaultSelectAll' => function () {
                if (is_null($this->getQuery()->columns)) {
                    $this->select($this->getQuery()->from.'.*');
                }

                return $this;
            },

            'joinRelation' => function (string $relationName, $operator = '=') {
                $relation = $this->getRelation($relationName);

                return $this->join(
                    $relation->getRelated()->getTable(),
                    $relation->getQualifiedForeignKeyName(),
                    $operator,
                    $relation->getQualifiedOwnerKeyName()
                );
            },

            'leftJoinRelation' => function (string $relationName, $operator = '=') {
                $relation = $this->getRelation($relationName);

                return $this->leftJoin(
                    $relation->getRelated()->getTable(),
                    $relation->getQualifiedForeignKeyName(),
                    $operator,
                    $relation->getQualifiedParentKeyName()
                );
            },

            'map' => function (callable $callback) {
                return $this->get()->map($callback);
            },

            'filter' => function (callable $callback) {
                return $this->get()->filter($callback);
            },

            'whereLike' => function (array $attributes, string $terms, int $charLength = 10) {

                $this->where(function (EloquentBuilder $query) use ($attributes, $terms, $charLength) {
                    foreach (Arr::wrap($attributes) as $attribute)
                    {

                        // If it's a single item, wrap the value in an array e.g. $term = [$term];
                        foreach (Arr::wrap($terms) as $term)
                        {

                            // When whereLike contains a relationship.value, search the relationship value
                            $query->when(Str::contains($attribute, '.'), function (EloquentBuilder $query) use ($attribute, $term, $charLength) {
                                $buffer         = explode('.', $attribute);
                                $attributeField = array_pop($buffer);
                                $relationPath   = implode('.', $buffer);

                                // Validating if the relationship exists on the current query
                                $query->orWhereHas($relationPath, function (EloquentBuilder $query) use ($attributeField, $term, $charLength) {
                                    $query->where($attributeField, 'LIKE', "%{$term}%");

                                    $terms = pheg()->str()->buildSearchTerms($term, $charLength);
                                    if (count($terms) >= 1)
                                    {
                                        foreach ($terms as $searchTerm)
                                        {
                                            $query->orWhere($attributeField, 'LIKE', "%{$searchTerm}%");
                                        }
                                    }

                                });
                            },

                                // A fallback for when the string DOES not contain a relationship
                                function (EloquentBuilder $query) use ($attribute, $term, $charLength) {
                                    $query->orWhere($attribute, 'LIKE', "%{$term}%");

                                    $terms = pheg()->str()->buildSearchTerms($term, $charLength);
                                    if (count($terms) >= 1)
                                    {
                                        foreach ($terms as $searchTerm)
                                        {
                                            $query->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
                                        }
                                    }
                                }

                            );
                        }

                    }
                });

                // Return the $query, so you can call other methods like ->get(), ->first(), ->where(), etc
                return $this;
            },

            'whereNot' => function ($column, $operator = null, $value = null) {
                return $this->where($column, $operator, $value, 'and not');
            },

            'orWhereNot' => function ($column, $operator = null, $value = null) {
                return $this->where($column, $operator, $value, 'or not');
            },

        ])
            ->each(fn ($function, $macro)   => EloquentBuilder::macro($macro, $function));

        Collection::make([
            'paginateFirstDifferent' => \Simtabi\Laranail\Nails\Macros\Supports\Paginators\BuilderMacros\PaginateFirstDifferent::class,
            'paginateWithPrevious'   => \Simtabi\Laranail\Nails\Macros\Supports\Paginators\BuilderMacros\PaginateWithPrevious::class,
        ])
            ->each(fn ($class, $macro)   => EloquentBuilder::macro($macro, app($class)()));

        /**
         * dd()
         * Only enable this macro on local and testing environment
         */
        if (app()->environment('local', 'testing')) {
            QueryBuilder::macro('dd', function($params = null) {
                $arr = array_filter([$params, $this->toRawSql()]);

                call_user_func_array('dd', $arr);
            });

            EloquentBuilder::macro('dd', function($params = null) {
                $arr = array_filter([$params, $this->toRawSql()]);
                call_user_func_array('dd', $arr);
            });
        }

    }

    private function loadDbBlueprintMacros()
    {

        Collection::make([

            'addAcceptance' => function ($value, $table_by = 'users', $is_default = true) {
                $this->is($value, $is_default);
                $this->at($value);
                $this->by($table_by, $value);
                $this->remarks($value . '_remarks');

                return $this;
            },

            'status' => function ($key = 'status', $default = true) {
                return $this->boolean($key)->default($default)->comment('Status');
            },

            'is' => function ($key = 'activated', $default = true, $prefix = 'is_') {
                return $this->status($prefix . $key, $default)->comment('Is it ' . $key . '?');
            },

            'at' => function ($key = 'activated', $suffix = '_at') {
                return $this->datetime($key . $suffix)->nullable()->comment('Event occured at Date & Time');
            },

            'by' => function ($table, $key = null, $nullable = true, $bigInteger = false, $suffix = '_by') {
                return $this->addForeign($table, [
                    'fk' => (! is_null($key) ? $key . $suffix : null),
                    'nullable' => $nullable,
                    'bigInteger' => $bigInteger,
                ]);
            },

            'user' => function ($nullable = false) {
                return $this->addForeign('users', ['nullable' => $nullable])->comment('Owner of the record.');
            },

            'standardTime' => function () {
                $this->softDeletes();
                $this->timestamps();
            },

            'addForeign' => function ($table, $options = []) {
                $fk = (isset($options['fk']) && ! empty($options['fk'])) ?
                    $options['fk'] : strtolower(Str::singular($table)) . '_id';

                $reference = (isset($options['reference']) && ! empty($options['reference'])) ?
                    $options['reference'] : 'id';

                if (isset($options['bigInteger']) && true == $options['bigInteger']) {
                    $schema = $this->unsignedBigInteger($fk)->index();
                } else {
                    $schema = $this->unsignedInteger($fk)->index();
                }

                if (isset($options['nullable']) && true == $options['nullable']) {
                    $schema->nullable();
                }

                if (! isset($options['no_reference'])) {
                    $this->referenceOn($fk, $table, $reference);
                }

                return $schema;
            },

            'addNullableForeign' => function ($table, $fk, $bigInteger = false) {
                return $this->addForeign($table, ['nullable' => true, 'fk' => $fk, 'bigInteger' => $bigInteger])->comment('Nullable FK for ' . $table);
            },

            'referenceOn' => function ($key, $table, $reference = 'id') {
                return $this->foreign($key)
                    ->references($reference)
                    ->on($table);
            },

            'belongsTo' => function ($table, $key = null, $bigInteger = false, $reference = 'id') {
                if (is_null($key)) {
                    $key = strtolower(Str::singular($table)) . '_id';
                }

                return $this->addForeign($table, ['fk' => $key, 'reference' => $reference, 'bigInteger' => $bigInteger])->comment('FK for ' . $table);
            },

            'nullableBelongsTo' => function ($table, $key = null, $bigInteger = false, $reference = 'id') {
                if (is_null($key)) {
                    $key = strtolower(Str::singular($table)) . '_id';
                }

                return $this->addNullableForeign($table, $key, $bigInteger);
            },

            'uuid' => function ($length = 64) {
                return $this->string('uuid', $length)->comment('UUID');
            },

            'hashslug' => function ($length = 64) {
                return $this->string('hashslug')
                    ->length($length)
                    ->nullable()
                    ->unique()
                    ->index()
                    ->comment('Hashed Slug');
            },

            'slug' => function () {
                return $this->string('slug')
                    ->nullable()
                    ->unique()
                    ->index()
                    ->comment('Slug');
            },

            'createIndexIfNotExists' => function ($columns, $name = null, $connection = null) {
                return $this->hasIndex($columns, $connection)
                    ? $this
                    : $this->index($columns, $name ?: $this->createIndexNameByColumns($columns));
            },

            'hasIndex' => function ($columns) {
                return $this->getIndexNameByColumns($columns) !== null;
            },

            'getIndexNameByColumns' => function ($columns) {
                if (is_string($columns)) {
                    $columns = [$columns];
                }

                $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
                $indices = $schemaManager->listTableIndexes($this->getTable());
                $filteredIndices = collect($indices)->filter(function (Index $index) use ($columns) {
                    return pheg()->arr()->compareArray($index->getColumns(), $columns);
                });

                if ($filteredIndices->isNotEmpty()) {
                    return $filteredIndices->keys()->first();
                }

                return null;
            },

            'createIndexNameByColumns' => function ($columns) {
                if (is_string($columns)) {
                    $columns = [$columns];
                }

                $index = $this->createIndexName('index', $columns);

                if (strlen($index) > 64) {
                    $index = implode('_', $columns);
                }

                return $index;
            },

            'dropIndexIfExists' => function ($columns) {
                if ($this->hasIndex($columns))
                {
                    return $this->dropIndex($this->getIndexNameByColumns($columns));
                }

                return $this;
            },

            'ordering' => function ($key = 'ordering', $length = 10) {
                return $this->string($key, $length)
                    ->nullable()
                    ->comment('Ordering');
            },

            'percent' => function ($key = 'percent') {
                return $this->decimal($key, 5, 2)->default(0)->comment('Percentage');
            },

            'expired' => function () {
                $this->boolean('is_expired')->default(false)->comment('Is Expired in Boolean');
                $this->datetime('expired_at')->nullable()->comment('Expired Date Time');

                return $this;
            },

            'money' => function ($label = 'money', $percision = 8, $scale = 2) {
                return $this->decimal($label, $percision, $scale)
                    ->nullable()
                    ->default(0.00)
                    ->comment('Money');
            },

            'amount' => function ($label = 'amount') {
                return $this->bigInteger($label)
                    ->nullable()
                    ->default(0)
                    ->comment('Big amount of money');
            },

            'smallAmount' => function ($label = 'amount') {
                return $this->integer($label)
                    ->nullable()
                    ->default(0)
                    ->comment('Small amount of money');
            },

            'label' => function ($value = 'label', $length = 255) {
                return $this->string($value, $length)->nullable()->comment($value);
            },

            'name' => function ($value = 'name', $length = 255) {
                return $this->string($value, $length)->nullable()->comment($value);
            },

            'title' => function ($value = 'title', $length = 255) {
                return $this->string($value, $length)->nullable()->comment($value);
            },

            'code' => function ($key = 'code', $length = 20) {
                return $this->string($key, $length)
                    ->nullable()
                    ->index()
                    ->comment('Code');
            },

            'reference' => function ($label = 'reference', $length = 64) {
                return $this->string('reference', $length)
                    ->nullable()
                    ->unique()
                    ->index()
                    ->comment('Reference');
            },

            'remarks' => function ($value = 'remarks') {
                return $this->text($value)->nullable()->comment('Remarks');
            },

            'description' => function ($label = 'description') {
                return $this->text($label)->nullable()->comment('Description');
            },

        ])
            ->reject(fn ($function, $macro) => Blueprint::hasMacro($macro))
            ->each(fn ($function, $macro)   => Blueprint::macro($macro, $function));

    }

    private function loadShovelMacros()
    {

        $this->app['router']->aliasMiddleware('ApiRequestMiddleware', \Simtabi\Laranail\Http\Middleware\ApiRequest::class);
        $this->app['router']->aliasMiddleware('ApiResponseMiddleware', \Simtabi\Laranail\Http\Middleware\ApiResponse::class);

        $withMeta = function ($key, $value) {
            Arr::set($this->additionalMeta, $key, $value);
            return $this;
        };

        if (! Response::hasMacro('withMeta'))
        {
            Response::macro('withMeta', $withMeta);
        }

        if (! JsonResponse::hasMacro('withMeta'))
        {
            JsonResponse::macro('withMeta', $withMeta);
        }

        if (! ResponseFactory::hasMacro('withMeta'))
        {
            ResponseFactory::macro('withMeta', $withMeta);
        }

    }

    private function loadArrMacros()
    {

        if (! Arr::hasMacro('hasSameKeysAndValues'))
        {
            Arr::macro('hasSameKeysAndValues', function(array $a, array $b, bool $strict = false) {
                return pheg()->arr()->compareArrayKeyValuesSimilarity($a, $b, $strict);
            });
        }

    }

    private function loadResponseMacros()
    {

        foreach (['success', 'info', 'danger', 'warning'] as $type)
        {
            if (! RedirectResponse::hasMacro($type))
            {
                RedirectResponse::macro($type, function ($message) use ($type) {
                    return $this->with($type, $message);
                });
            }
        }

        $translationKey = 'laranail::messages.';

        Collection::make([

            'determineMessage' => function ($message = null) {
                return $message instanceof Exception ? $message->getMessage() : ($message ?? null);
            },

            'created' => function ($route = null) use ($translationKey) {
                return $this->with('success', (null === $route ? trans("{$translationKey}created-min") : trans("{$translationKey}created", ['url' => $route])));
            },

            'updated' => function ($message = null) use ($translationKey) {
                return $this->with('success', $message ?? trans("{$translationKey}updated"));
            },

            'deleted' => function ($message = null) use ($translationKey) {
                return $this->with('success', $message ?? trans("{$translationKey}deleted"));
            },

            'error' => function ($message) {
                return $this->with('error', $this->determineMessage($message));
            },

            'errorNotFound' => function ($message = null) use ($translationKey) {
                return $this->with('error', $this->determineMessage($message) ?? trans("{$translationKey}not-found"));
            },

            'authorized' => function ($message = null) use ($translationKey) {
                return $this->with('success', $message ?? trans("{$translationKey}authorized"));
            },

            'unAuthorized' => function ($message = null) use ($translationKey) {
                $message = $this->determineMessage($message);

                return $this->with('error', $message ?? trans("{$translationKey}un-authorized"));
            },

        ])
            ->reject(fn ($function, $macro) => RedirectResponse::hasMacro($macro))
            ->each(fn ($function, $macro)   => RedirectResponse::macro($macro, $function));


        Collection::make([

            // MACRO:       'success' JSON response (200)
            'success' => function ($data) {
                return Response::json([
                    'errors' => false,
                    'data'   => $data,
                ]);
            },

            // MACRO:       'no content' JSON response (204)
            'noContent' => function () {
                return Response::json(null, 204);
            },

            // MACRO:       'error' JSON response
            'error' => function ($message, $status = 400) {
                return Response::json([
                    'errors'  => true,
                    'message' => $message,
                ], $status);
            },

        ])
            ->reject(fn ($function, $macro) => Response::hasMacro($macro))
            ->each(fn ($function, $macro)   => Response::macro($macro, $function));

    }

    private function loadRequestMacros()
    {

        Collection::make([

            'cast' => function ($key, $callback) {
                $keys = is_array($key) ? $key : func_get_args();

                $keys = array_filter($keys, function ($key) {
                    return is_scalar($key);
                });

                $result = [];

                foreach ($keys as $k) {
                    $result[$k] = $callback($this->input($k));
                }

                return $key === $keys ? $result : head($result);
            },

            'bool' => function ($key) {
                return $this->cast($key, function ($value) {
                    return pheg()->transfigure()->toBool($value);
                });
            },

            'int' => function ($key) {
                return $this->cast($key, function ($value) {
                    return pheg()->transfigure()->toInteger($value);
                });
            },

            'float' => function ($key) {
                return $this->cast($key, function ($value) {
                    return pheg()->transfigure()->toFloat($value);
                });
            },

            'validate' => function ($rules, $messages = [], $customAttributes = []) {
                $this->lastValidated = array_keys($rules);

                (new class() {
                    use ValidatesRequests;
                })->validate($this, $rules, $messages, $customAttributes);
            },

            'validated' => function ($rules) {
                $this->validate($rules);

                return $this->only(array_keys($rules));
            },

        ])
            ->reject(fn ($function, $macro) => Request::hasMacro($macro))
            ->each(fn ($function, $macro)   => Request::macro($macro, $function));

    }

    private function loadTestResponse()
    {

        Collection::make([

            'showError' => function () {
                if ($this->isServerError()) {
                    dd(array_except($this->getOriginalContent(), ['trace']));
                }
            },

            'dd' => function () {
                $this->showError();

                dd($this->getContent());
            },

            'ddO' => function () {
                $this->showError();

                dd($this->getOriginalContent());
            },

            'ddo' => function () {
                dd($this->getOriginalContent());
            },

            'ddr' => function () {
                ddr($this->getOriginalContent());
            },

        ])
            ->reject(fn ($function, $macro) => TestResponse::hasMacro($macro))
            ->each(fn ($function, $macro)   => TestResponse::macro($macro, $function));

    }

    private function loadMixins()
    {
        EloquentBuilder::mixin(new FactoryBuilderMixin);
    }

}
