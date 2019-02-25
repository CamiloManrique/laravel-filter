<?php
/**
 * Created by PhpStorm.
 * User: Camilo
 * Date: 24/2/2019
 * Time: 19:48
 */

namespace CamiloManrique\LaravelFilter\Parsers;


class QueryParser
{
    public $column;
    public $modifier;

    public function __construct($column, $modificator)
    {
        $this->column = $column;
        $this->modifier = $modificator;
    }

    /**
     *
     * Get a new instance from a string with the structure "column{relationship_separator}modificator"
     * The default separator is "/" so the structure required by default is "column/modificator"
     * It's also possible that there are no modificator on the input, only column name.
     *
     * @param string $string
     * @return QueryParser
     */
    public static function parseFromString(string $string){
        $filter_attr = preg_split("/".preg_quote(config("filter.separators.query_modifier"), "/")."/", $string);
        if(count($filter_attr ) > 1){
            $column = $filter_attr[0];
            $modificator = $filter_attr[1];
        }
        else{
            $modificator = null;
            $column = $filter_attr[0];
        }

        return new self($column, $modificator);
    }



}