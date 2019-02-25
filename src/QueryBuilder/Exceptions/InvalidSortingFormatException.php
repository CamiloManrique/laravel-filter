<?php
/**
 * Created by PhpStorm.
 * User: Camilo
 * Date: 3/3/2019
 * Time: 11:01
 */

namespace CamiloManrique\LaravelFilter\QueryBuilder\Exceptions;


use CamiloManrique\LaravelFilter\Exceptions\LaravelFilterException;
use Throwable;

class InvalidSortingFormatException extends LaravelFilterException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        $message = "The sort format is not a valid format";
        parent::__construct($message, $code, $previous);
    }

}