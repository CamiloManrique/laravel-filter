<?php
/**
 * Created by PhpStorm.
 * User: Camilo
 * Date: 3/3/2019
 * Time: 11:46
 */

namespace CamiloManrique\LaravelFilter\Exceptions;

use Throwable;

class InvalidArgumentException extends LaravelFilterException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        $message = "Argument must be a Request, Collection or array";
        parent::__construct($message, $code, $previous);
    }

}