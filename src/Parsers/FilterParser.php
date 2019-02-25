<?php
/**
 * Created by PhpStorm.
 * User: Camilo
 * Date: 24/2/2019
 * Time: 19:28
 */

namespace CamiloManrique\LaravelFilter\Parsers;


class FilterParser
{
    public $relationship;
    public $attribute;

    public function __construct($attribute, $relationship = null)
    {
        $this->relationship = $relationship;
        $this->attribute = $attribute;
    }

    /**
     *
     * Get a new instance from a string with the structure "relationship{relationship_separator}column"
     * The default separator is "@" so the structure required by default is "relationship@column"
     * It's also possible that there are no relationship on the input, only column name.
     *
     * @param string $string
     * @return FilterParser
     */
    public static function parseFromString(string $string){
        $filter_attr = preg_split("/".preg_quote(config("filter.separators.relationship"), "/")."/", $string);
        if(count($filter_attr ) > 1){
            $relationship = $filter_attr[0];
            $attribute = $filter_attr[1];
        }
        else{
            $relationship = null;
            $attribute = $filter_attr[0];
        }

        return new self($attribute, $relationship);
    }


}