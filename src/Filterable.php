<?php

namespace CamiloManrique\LaravelFilter;

use CamiloManrique\LaravelFilter\Exceptions\InvalidArgumentException;
use CamiloManrique\LaravelFilter\Exceptions\UnknownColumnException;
use CamiloManrique\LaravelFilter\Tests\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use CamiloManrique\LaravelFilter\QueryBuilder\ColumnFilters;
use CamiloManrique\LaravelFilter\QueryBuilder\Relationships;
use CamiloManrique\LaravelFilter\QueryBuilder\Sorting;

trait Filterable{

//    private static function getRequestFields(Request $request){
//        $keyword = config("filter.keywords.fields");
//        return !is_null($request->input($keyword)) ? explode(",", $request->input($keyword)) : null;
//    }


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
     * @throws InvalidArgumentException
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
                throw new InvalidArgumentException();
            }
        }
        elseif (is_array($input)){
            return collect($input);
        }
        else{
            throw new InvalidArgumentException();
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
        $filters = ColumnFilters::getFilters($input);
        $relationships = Relationships::getRelationships($input);
        $sorting = Sorting::getSorting($input);


        $query = $builder;
        $query = Relationships::addRelationships($query, $relationships);
        $query = ColumnFilters::addFilters($query, $filters);

        if(!is_null($sorting)){
            $query = Sorting::sortResult($query, $sorting);
        }

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
     * @throws InvalidArgumentException
     * @throws UnknownColumnException
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

            return $result;
        }

        $page_size_keyword = config("filter.keywords.page_size");

        try{
            if($input->has($page_size_keyword)){
                $query = $query->paginate($input->get($page_size_keyword));
                $query->withPath((\request()->url()."?$query_string"));
            }
            else{
                $query = $query->get();
            }
        }
        catch (QueryException $exception){
            if(preg_match("/Unknown column '(.*?)'/", $exception->getMessage(), $matches)){
                throw new UnknownColumnException($matches[1], class_basename($query->getModel()));
            }
            else{
                throw $exception;
            }
        }

        return $query;

    }



}