<?php
/**
 * Created by PhpStorm.
 * User: Camilo
 * Date: 24/2/2019
 * Time: 18:51
 */

namespace CamiloManrique\LaravelFilter;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Filters
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

            $filter_attr = preg_split("/".config("filter.relationship_separator")."/", $key);

            if(count($filter_attr) == 2){
                $relationship = $filter_attr[0];
                $column_unparsed = $filter_attr[1];
                if(!in_array($relationship, $relationships)){
                    array_push($relationships, $relationship);
                }
                $query = self::_processFilter($query, $column_unparsed, $value, $relationship);
            }
            else{
                $column_unparsed = $filter_attr[0];
                $query = self::_processFilter($query, $column_unparsed, $value);
            }

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
        $parsed = preg_split("/".config("filter.column_query_modificator")."/", $column_unparsed);

        $column = $parsed[0];

        if($column === config("filter.keywords.model_count")){

            $fk = $query->getRelation("posts");
            $fk = $fk->getForeignKeyName();

            return $query->whereHas($relationship, function($q) use ($value, $fk){
                $q->groupBy($fk)->havingRaw('COUNT(*) = ?', [$value]);
            });

        }

        if(count($parsed) == 2){
            $modificator = $parsed[1];
            switch($modificator){
                case "start":
                    $args = [$column, ">=", $value];
                    break;
                case "end":
                    $args = [$column, "<=", $value];
                    break;
                case "like":
                    $args = [$column, "LIKE", "%$value%"];
                    break;
                case "not":
                    $args = [$column, "!=", "$value"];
                    break;
                default:
                    $args = [$column, $value];
                    break;
            }
        }
        else{
            $args = [$column, $value];
        }

        if(!is_null($relationship)){
            return $query->whereHas($relationship, function($q) use($args, $value){
                if(is_array($value)){
                    $q->whereIn(...$args);
                }
                else{
                    $q->where(...$args);
                }
            });
        }
        else{
            if(is_array($value)){
                return $query->whereIn(...$args);

            }
            else{
                return $query->where(...$args);
            }

        }
    }


}