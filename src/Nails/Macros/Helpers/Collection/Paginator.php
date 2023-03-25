<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Helpers\Collection;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Container\Container;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator as Pager;
use Illuminate\Support\Collection;

class Paginator
{
    public static function paginate(mixed $results, int $pageSize, string $pageName = 'page')
    {

        if ((!$results instanceof EloquentCollection) || (!$results instanceof Collection)) {
            $results = new Collection($results);
        }


        $page  = Pager::resolveCurrentPage($pageName);
        $total = $results->count();

        return self::paginator($results->forPage($page, $pageSize), $total, $pageSize, $page, [
            'path'     => Pager::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);

    }

    /**
     * Create a new length-aware paginator instance.
     *
     * @param Collection $items
     * @param int $total
     * @param int $perPage
     * @param int $currentPage
     * @param array $options
     * @return LengthAwarePaginator
     * @throws BindingResolutionException
     */
    protected static function paginator(Collection $items, int $total, int $perPage, int $currentPage, array $options)
    {
        return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
            'items', 'total', 'perPage', 'currentPage', 'options'
        ));
    }

}