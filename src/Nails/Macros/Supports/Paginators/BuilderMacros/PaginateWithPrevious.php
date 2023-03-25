<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Paginators\BuilderMacros;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Paginate the given collection and take all records from first page to current.
 *
 * @param int $perPage
 * @param string $pageName
 * @param int|null $page
 * @param int|null $total
 * @param array $options
 *
 * @mixin Builder
 *
 * @return LengthAwarePaginator
 */
class PaginateWithPrevious
{
    public function __invoke()
    {
        return function (int $perPage = 15, string $pageName = 'page', int $page = null, int $total = null, array $options = []): LengthAwarePaginator {
            $total = $total ?: $this->count();

            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            $options += [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ];

            $items = $this->take($page * $perPage)->get();

            return new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                $options
            );
        };
    }
}