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
        $filters = Filters::getFilters($input);
        $relationships = Relationships::getRelationships($input);
        $sorting = Sorting::getSorting($input);


        $query = $builder;
        $query = Relationships::addRelationships($query, $relationships);
        $query = Filters::addFilters($query, $filters);

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