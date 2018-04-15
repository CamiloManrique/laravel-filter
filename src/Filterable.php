<?php

namespace CamiloManrique\LaravelFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait Filterable{

//    private static function getRequestFields(Request $request){
//        $keyword = config("filter.keywords.fields");
//        return !is_null($request->input($keyword)) ? explode(",", $request->input($keyword)) : null;
//    }

    /**
     *
     * Get a two value array containing the sorting column and the direction
     *
     * @param Collection $params
     * @return array
     *
     */
    private static function _getSorting(Collection $params){
        $keyword = config("filter.keywords.sorting");
        return $params->has($keyword) ? explode("/", $params->get($keyword)) : null;
    }

    /**
     *
     * Get an array containing only the relationships
     *
     * @param Collection $params
     * @return array
     *
     */
    private static function _getRelationships(Collection $params){
        $keyword = config("filter.keywords.relationships");
        return $params->has($keyword) ? explode(",", $params->get($keyword)) : [];
    }

    /**
     *
     * Get an array containing only the column filters
     *
     * @param Collection $params
     * @return array
     *
     */
    private static function _getFilters(Collection $params){
        $keywords = config("filter.keywords") + config("filter.aditional_keywords");
        return $params->filter(function ($value, $key) use ($keywords){
            return !in_array($key, $keywords);
        })->all();
    }

    /**
     *
     * Process a filter and append it to the query
     *
     * @param Collection $params
     * @return array
     *
     */
    private static function _processFilter($query, $column_unparsed, $value, $relationship=null){
        $parsed = preg_split("/".config("filter.column_query_modificator")."/", $column_unparsed);
        if(count($parsed) == 2){
            switch($parsed[1]){
                case "start":
                    $args = [$parsed[0], ">=", $value];
                    break;
                case "end":
                    $args = [$parsed[0], "<=", $value];
                    break;
                case "like":
                    $args = [$parsed[0], "LIKE", "%$value%"];
                    break;
                case "not":
                    $args = [$parsed[0], "!=", "$value"];
                    break;
                default:
                    $args = [$parsed[0], $value];
                    break;
            }
        }
        else{
            $args = [$parsed[0], $value];
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

    private static function _addFilters($query, $filters){
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

    private static function _addRelationships($query, $relationships){
        $query = $query->with($relationships);
        return $query;
    }

    private static function _sortResult($query, $sorting){

        if(is_null($sorting)){
            return $query;
        }
        else{
            return $query->orderBy($sorting[0], $sorting[1]);
        }
    }

//    private static function addFields($query, $fields){
//
//        $relationships = array();
//        foreach($fields as $field){
//            $parse_field = preg_split("/@/", $field);
//
//            if(count($parse_field) == 2){
//                $relationship = $parse_field[0];
//                $column = $parse_field[1];
//                if(array_key_exists($relationship, $relationships)){
//                    array_push($relationships[$relationship], $column);
//                }
//                else{
//                    $relationships[$relationship] = array($column);
//                }
//            }
//            else{
//                $column = $parse_field[0];
//            }
//        }
//
//        var_dump($relationships);
//
//        foreach($relationships as $key => $value){
//            $query = $query->with($key.":".implode(",", $value));
//        }
//
//        $query = $query->with("account_info:username");
//
//        return $query;
//    }

    /**
     *
     * Turn the input into a collection. Also throws and InvalidArgumentException if the argument is not a
     * Request or an array.
     *
     * @param Request|array|Collection|null $input
     * @return Collection
     *
     *
     * @throws \InvalidArgumentException
     */

    private static function _normalizeArguments($input){
        if(is_null($input)){
            return collect();
        }
        if (is_object($input)){
            if(get_class($input) == Request::class)
                return collect($input->all());
            elseif (get_class($input) == Collection::class){
                return $input;
            }
            else{
                throw new \InvalidArgumentException("Argument must be a Request, Collection or array");
            }
        }
        elseif (is_array($input)){
            return collect($input);
        }
        else{
            throw new \InvalidArgumentException("Argument must be a Request, Collection or array");
        }
    }

    /**
     *
     * Get the query for selecting that match the request filters
     *
     * @param Request|array|Collection|null $input
     * @param Builder|null $builder
     * @return
     *
     *
     * @throws \InvalidArgumentException
     */

    public static function filter($input = null, $builder){

        $input = self::_normalizeArguments($input);

        //$fields = self::getRequestFields($request);
        $filters = self::_getFilters($input);
        $relationships = self::_getRelationships($input);
        $sorting = self::_getSorting($input);


        $query = $builder;
        $query = self::_addRelationships($query, $relationships);
        $query = self::_addFilters($query, $filters);
        $query = self::_sortResult($query, $sorting);

        return $query;


    }

    /**
     *
     * Get the collection containing the models matching the request filters
     *
     * @param Request|array|Collection|null $input
     * @param Builder|null $builder
     * @return Collection
     *
     *
     * @throws \InvalidArgumentException
     */

    public static function filterAndGet($input = null, $builder){

        $input = self::_normalizeArguments($input);

        $query_string = http_build_query(\request()->except("page"));

        $query = self::filter($input, $builder);

        if($input->has("sum")){
            $aggregates = [];
            $columns = explode(",", $input->get("sum"));

            foreach($columns as $column){
                array_push($aggregates, DB::raw("SUM($column) as $column"));
            }

            $result = $query->get($aggregates)[0];

            foreach ($columns as $column){
                $result->$column = intval($result->$column);
            }

            return $result;
        }

        if($input->has("page_size")){
            $query = $query->paginate($input->get("page_size"));
            $query->withPath((\request()->url()."?$query_string"));
        }
        else{
            if(config("filter.paginate_by_default")){
                $query = $query->paginate(config("filter.page_size"));
                $query->withPath((\request()->url()."?$query_string"));
            }
            else{
                $query = $query->get();
            }
        }

        return $query;

    }



}