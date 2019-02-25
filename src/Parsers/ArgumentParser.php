<?php
/**
 * Created by PhpStorm.
 * User: Camilo
 * Date: 24/2/2019
 * Time: 21:20
 */

namespace CamiloManrique\LaravelFilter\Parsers;


use CamiloManrique\LaravelFilter\Parsers\Exceptions\UnknownModifierException;

class ArgumentParser
{

    public function __construct($column, $value, $operator = null)
    {
        $this->column = $column;
        $this->value = $value;
        $this->operator = $operator;

    }

    /**
     * @param $column
     * @param string $value
     * @param mixed $modifier
     * @return ArgumentParser
     * @throws UnknownModifierException
     */
    public static function createArgsFromModifier(string $column, $value, string $modifier = null){

        $operator = null;
        if(!is_null($modifier)){
            switch($modifier){
                case "start":
                    $operator = ">=";
                    break;
                case "end":
                    $operator = "<=";
                    break;
                case "like":
                    $operator = "LIKE";
                    $value = "%$value%";
                    break;
                case "not":
                    $operator = "!=";
                    break;
                default:
                    throw new UnknownModifierException($modifier);
            }
        }

        return new self($column, $value, $operator);

    }

    public function toArray(){
        return !is_null($this->operator) ? [$this->column, $this->operator, $this->value] : [$this->column, $this->value];
    }

}