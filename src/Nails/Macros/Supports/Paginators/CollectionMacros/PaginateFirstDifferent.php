<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Paginators\CollectionMacros;

use Simtabi\Laranail\Nails\Macros\Supports\Paginators\FirstDifferentLengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Paginate the given collection but first page has different size then others.
 *
 * @param int $firstPerPage
 * @param int $nextPerPage
 * @param bool $withPrevious
 * @param string $pageName
 * @param int|null $page
 * @param int|null $total
 * @param array $options
 *
 * @mixin Collection
 *
 * @return FirstDifferentLengthAwarePaginator
 */
class PaginateFirstDifferent
{
    public function __invoke()
    {
        return function (int $firstPerPage, int $nextPerPage, bool $withPrevious = false, string $pageName = 'page', int $page = null, int $total = null, array $options = []): FirstDifferentLengthAwarePaginator {
            $total = $total ?: $this->count();

            $page = $page ?: FirstDifferentLengthAwarePaginator::resolveCurrentPage($pageName);

            $options += [
                'path' => FirstDifferentLengthAwarePaginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ];

            $perPage = $page === 1 ? $firstPerPage : $nextPerPage;
            $offset = ($page - 2) * $perPage + $firstPerPage;

            if ($withPrevious) {
                $items = $this->take($offset + $perPage)->get();
            } else {
                $items = $this->offset($offset)->limit($perPage)->get();
            }

            return new FirstDifferentLengthAwarePaginator(
                $items,
                $total,
                $firstPerPage,
                $nextPerPage,
                $perPage,
                $page,
                $options
            );
        };
    }
}