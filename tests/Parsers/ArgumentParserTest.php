<?php
/**
 * Created by PhpStorm.
 * User: Camilo
 * Date: 24/2/2019
 * Time: 22:53
 */

namespace CamiloManrique\LaravelFilter\Tests\Parsers;


use CamiloManrique\LaravelFilter\Parsers\ArgumentParser;
use CamiloManrique\LaravelFilter\Parsers\Exceptions\UnknownModifierException;
use CamiloManrique\LaravelFilter\Tests\TestCase;

class ArgumentParserTest extends TestCase
{

    private $column;
    private $value;

    protected function setUp()
    {
        parent::setUp();
        $this->column = "email";
        $this->value = "hello@example.com";
    }

    public function testCreateArgsWithStartModifier(){
        $args = ArgumentParser::createArgsFromModifier($this->column, $this->value, "start");
        $this->assertArraySubset([$this->column, ">=", $this->value], $args->toArray());
    }

    public function testCreateArgsWithEndModifier(){
        $args = ArgumentParser::createArgsFromModifier($this->column, $this->value, "end");
        $this->assertArraySubset([$this->column, "<=", $this->value], $args->toArray());
    }

    public function testCreateArgsWithLikeModifier(){
        $args = ArgumentParser::createArgsFromModifier($this->column, $this->value, "like");
        $this->assertArraySubset([$this->column, "LIKE", "%".$this->value."%"], $args->toArray());
    }

    public function testCreateArgsWithNotModifier(){
        $args = ArgumentParser::createArgsFromModifier($this->column, $this->value, "not");
        $this->assertArraySubset([$this->column, "!=", $this->value], $args->toArray());
    }

    public function testCreateArgsWithoutModifier(){
        $args = ArgumentParser::createArgsFromModifier($this->column, $this->value, null);
        $this->assertArraySubset([$this->column, $this->value], $args->toArray());
    }

    public function testCreateArgsWithInvalidModifier(){
        $this->expectException(UnknownModifierException::class);
        ArgumentParser::createArgsFromModifier($this->column, $this->value, "invalid_modifier");
    }

}