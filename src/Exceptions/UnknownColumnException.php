<?php
/**
 * Created by PhpStorm.
 * User: Camilo
 * Date: 3/3/2019
 * Time: 22:03
 */

namespace CamiloManrique\LaravelFilter\Exceptions;


use Throwable;

class UnknownColumnException extends LaravelFilterException
{
    public function __construct(string $column, string $model, int $code = 0, Throwable $previous = null)
    {
        $message = "The column $column does not exist on model $model";
        parent::__construct($message, $code, $previous);
    }

}