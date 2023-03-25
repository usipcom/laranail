<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Eloquent\Models\Traits;

trait HasScopes
{

    public function scopeWithWhereHas($query, $relation, $constraint)
    {
        return $query->whereHas($relation, $constraint)->with([$relation => $constraint]);
    }

    protected function scopeSearch($query, $term, array $searchable = [])
    {
       $buildWildCards = function ($term) {
            if ($term == "") {
                return $term;
            }

            // Strip MySQL reserved symbols
            $term  = str_replace(['-', '+', '<', '>', '@', '(', ')', '~'], '', $term);

            $words = explode(' ', $term);
            foreach($words as $idx => $word) {
                // Add operators, so we can leverage the boolean mode of
                // fulltext indices.
                $words[$idx] = "+" . $word . "*";
            }

            return implode(' ', $words);
        };

       if (empty($searchable)) {
           if (property_exists($this, 'searchable')) {
               $searchable = $this->searchable;
           }
       }

            $columns = implode(',', $this->searchable);

        // Boolean mode allows us to match john* for words starting with john
        // (https://dev.mysql.com/doc/refman/5.6/en/fulltext-boolean.html)
        $query->whereRaw(
            "MATCH ({$columns}) AGAINST (? IN BOOLEAN MODE)",
            $buildWildCards($term)
        );

        return $query;
    }

}
