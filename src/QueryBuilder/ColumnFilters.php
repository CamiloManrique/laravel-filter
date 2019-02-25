<?php
/**
 * Created by PhpStorm.
 * User: Camilo
 * Date: 24/2/2019
 * Time: 18:51
 */

namespace CamiloManrique\LaravelFilter\QueryBuilder;


use CamiloManrique\LaravelFilter\Parsers\ArgumentParser;
use CamiloManrique\LaravelFilter\Parsers\FilterParser;
use CamiloManrique\LaravelFilter\Parsers\QueryParser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ColumnFilters
{
    /**
     *
     * Get an array containing only the column filters
     *
     * @param Collection $params
     * @return array
     *
     */
    public static function getFilters(Collection $params){
        $keywords = config("filter.keywords") + config("filter.aditional_keywords");
        return $params->filter(function ($value, $key) use ($keywords){
            return !in_array($key, $keywords);
        })->all();
    }

    /**
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    public static function addFilters($query, $filters){
        $relationships = [];
        foreach($filters as $key => $value){

            $filter = FilterParser::parseFromString($key);

            if(!is_null($filter->relationship)){
                if(!in_array($filter->relationship, $relationships)){
                    array_push($relationships, $filter->relationship);
                }
            }

            $query = self::_processFilter($query, $filter->attribute, $value, $filter->relationship);

        }
        if(count($relationships) > 0){
            $query = $query->with($relationships);
        }

        return $query;

    }

    /**
     *
     * Process a filter and append it to the query
     *
     * @param Builder $query
     * @return Builder
     *
     */
    private static function _processFilter($query, $column_unparsed, $value, $relationship=null){

        $column_query = QueryParser::parseFromString($column_unparsed);

        $column = $column_query->column;
        $modifier = $column_query->modifier;

        if($column === config("filter.keywords.model_count")){

            $fk = $query->getRelation("posts");
            $fk = $fk->getForeignKeyName();

            return $query->whereHas($relationship, function($q) use ($value, $fk){
                $q->groupBy($fk)->havingRaw('COUNT(*) = ?', [$value]);
            });
        }

        $args = ArgumentParser::createArgsFromModifier($column, $value, $modifier);

        if(!is_null($relationship)){
            return self::appendArgsToRelationship($query, $relationship, $args);
        }
        else{
            return self::appendArgs($query, $args);
        }
    }


    /**
     * @param Builder $query
     * @param $relationship
     * @param ArgumentParser $args
     * @return Builder
     */
    private static function appendArgsToRelationship(Builder $query, $relationship, ArgumentParser $args){
        return $query->whereHas($relationship, function($q) use($args){
            if(is_array($args->value)){
                $q->whereIn(...$args->toArray());
            }
            else{
                $q->where(...$args->toArray());
            }
        });
    }

    /**
     * @param Builder $query
     * @param ArgumentParser $args
     * @return Builder
     */
    private static function appendArgs(Builder $query, ArgumentParser $args){
        if(is_array($args->value)){
            return $query->whereIn(...$args->toArray());
        }
        else{
            return $query->where(...$args->toArray());
        }
    }


}