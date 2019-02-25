<?php

namespace CamiloManrique\LaravelFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Relationships
{

    /**
     *
     * Get an array containing only the relationships
     *
     * @param Collection $params
     * @return array
     *
     */
    public static function getRelationships(Collection $params){
        $keyword = config("filter.keywords.relationships");
        return $params->has($keyword) ? explode(",", $params->get($keyword)) : [];
    }

    /**
     *
     * Add relationships to a model query
     *
     * @param Builder $query
     * @param array $relationships
     * @return Builder
     */
    public static function addRelationships(Builder $query, array $relationships){
        $query = $query->with($relationships);
        return $query;
    }

}