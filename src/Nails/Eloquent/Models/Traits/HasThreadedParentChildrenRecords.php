<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Eloquent\Models\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasThreadedParentChildrenRecords
{
    /**
     * Loads only direct children - 1 level
     *
     * @return HasMany
     */
    public function parent()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    /**
     * Recursive, loads all descendants
     *
     * @return HasMany
     */
    public function children()
    {
        return $this
            ->hasMany(self::class, 'parent_id', 'id')
            ->with('parent')
            ->orderBy('created_at', 'ASC');
    }

    /**
     * Get all ticket responses in parent => children format
     *
     * @param string|null $id
     * @return mixed
     */
    public function getAsThreadedParentToChildren(?string $id = null)
    {

        $query = $this->whereNull('parent_id');

        if (!empty($id)) {
            $query->where('ticket_id', $id);
        }

        return $query->with(['children'])->get();
    }

    /**
     * Helper method to check if a comment has children.
     *
     * @return bool
     */
    public function hasChildren()
    {
        return $this->children()->count() >= 1;
    }

    public function isParent()
    {
        return empty($this->parent_id);
    }
}