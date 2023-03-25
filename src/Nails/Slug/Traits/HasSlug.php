<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Slug\Traits;

use Illuminate\Database\Query\Builder;
use Spatie\Sluggable\HasSlug as SpatieSlug;
use Spatie\Sluggable\SlugOptions;

trait HasSlug
{

    use SpatieSlug;

    /**
     * Set slug source field name
     *
     * @var string
     */
     protected string $slugSrcInputName  = 'name';

    /**
     * Set slug destination field name
     *
     * @var string
     */
     protected string $slugDestColumnName = 'slug';

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom($this->getSlugSrcInputName())->saveSlugsTo($this->getSlugDestColumnName());
    }

    /**
     * @return string
     */
    public function getSlugSrcInputName(): string
    {
        if (method_exists($this, 'setSlugSrcInputName')) {
            $field = $this->setSlugSrcInputName();
        }elseif (property_exists($this, 'slugSrcInputName')){
            $field = $this->slugSrcInputName;
        }

        return !empty($field) ? $field : 'name';
    }

    /**
     * @return string
     */
    public function getSlugDestColumnName(): string
    {
        if (method_exists($this, 'setSlugDestColumnName')) {
            $field = $this->setSlugDestColumnName();
        }elseif (property_exists($this, 'slugDestColumnName')){
            $field = $this->slugDestColumnName;
        }

        return !empty($field) ? $field : 'slug';
    }

    /**
     * Check that the slug doesn't already exist if updating
     *
     * @param string $slug
     *
     * @return string
     */
    public static function checkModelSlug(string $slug): string
    {
        if (self::slugExists($slug)) {
            return $slug . '-' . uniqid();
        }
        return $slug;
    }

    /**
     * Check if the slug exists
     *
     * @param string $slug
     * @param string $columnName
     * @return bool
     */
    public static function slugExists(string $slug, string $columnName = 'slug'): bool
    {
        return self::withoutGlobalScopes()->where($columnName, $slug)->exists();
    }

    /**
     * Return the model by the slug
     *
     * @param Builder $scope
     * @param string $slug
     * @param string $columnName
     * @return Builder
     */
    public function scopeBySlug(Builder $scope, string $slug, string $columnName = 'slug'): Builder
    {
        return $scope->where($columnName, $slug);
    }

}
