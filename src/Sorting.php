<?php

namespace CamiloManrique\LaravelFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Sorting
{
    /**
     *
     * Get a two value array containing the sorting column and the direction
     *
     * @param Collection $params
     * @return array
     *
     */
    public static function getSorting(Collection $params){
        $keyword = config("filter.keywords.sorting");
        return $params->has($keyword) ? explode("/", $params->get($keyword)) : null;
    }


    /**
     *
     * Add the sorting to the query
     *
     * @param Builder $query
     * @param array $sorting
     * @return Builder
     */
    public static function sortResult(Builder $query, array $sorting){
        return $query->orderBy($sorting[0], $sorting[1]);
    }

}