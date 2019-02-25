<?php
/**
 * Created by PhpStorm.
 * User: Camilo
 * Date: 24/2/2019
 * Time: 22:59
 */

namespace CamiloManrique\LaravelFilter\Parsers\Exceptions;


use CamiloManrique\LaravelFilter\Exceptions\LaravelFilterException;
use Throwable;

class UnknownModifierException extends LaravelFilterException
{
    public function __construct(string $modifier, int $code = 0, Throwable $previous = null)
    {
        $message = "The modifier $modifier is unknown";
        parent::__construct($message, $code, $previous);
    }

}