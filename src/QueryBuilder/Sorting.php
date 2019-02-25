<?php

namespace CamiloManrique\LaravelFilter\QueryBuilder;

use CamiloManrique\LaravelFilter\QueryBuilder\Exceptions\InvalidSortingFormatException;
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
     * @throws InvalidSortingFormatException
     */
    public static function getSorting(Collection $params){
        $keyword = config("filter.keywords.sorting");
        $sorting = $params->has($keyword) ? explode("/", $params->get($keyword)) : null;
        if(!is_null($sorting) && count($sorting) != 2){
            throw new InvalidSortingFormatException();
        }
        return $sorting;
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