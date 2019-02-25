<?php
/**
 * Created by PhpStorm.
 * User: Camilo
 * Date: 24/2/2019
 * Time: 21:50
 */

namespace CamiloManrique\LaravelFilter\Tests\Parsers;


use CamiloManrique\LaravelFilter\Parsers\QueryParser;
use CamiloManrique\LaravelFilter\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class QueryParserTest extends TestCase
{
    protected $separator;

    protected function setUp()
    {
        parent::setUp();

        // Better test with non default separators
        $this->separator = "@";
        Config::set("filter.separators.query_modifier", $this->separator);
    }

    public function testParsesValidStringWithModifier(){
        $parsed = QueryParser::parseFromString("column@modifier");
        $this->assertEquals("column", $parsed->column);
        $this->assertEquals("modifier", $parsed->modifier);
    }

    public function testParsesValidStringWithoutModifier(){
        $parsed = QueryParser::parseFromString("column-modifier");
        $this->assertEquals(null, $parsed->modifier);
        $this->assertEquals("column-modifier", $parsed->column);
    }

}