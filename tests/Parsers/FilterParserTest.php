<?php
/**
 * Created by PhpStorm.
 * User: Camilo
 * Date: 24/2/2019
 * Time: 21:40
 */

namespace CamiloManrique\LaravelFilter\Tests\Parsers;


use CamiloManrique\LaravelFilter\Parsers\FilterParser;
use CamiloManrique\LaravelFilter\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class FilterParserTest extends TestCase
{
    protected $separator;

    protected function setUp()
    {
        parent::setUp();

        // Better test with non default separators
        $this->separator = "/";
        Config::set("filter.separators.relationship", $this->separator);
    }

    public function testParsesValidStringWithRelationship(){
        $parsed = FilterParser::parseFromString("relationship".$this->separator."column@modifier");
        $this->assertEquals("relationship", $parsed->relationship);
        $this->assertEquals("column@modifier", $parsed->attribute);
    }

    public function testParsesValidStringWithoutRelationship(){
        $parsed = FilterParser::parseFromString("relationship-column@modifier");
        $this->assertEquals(null, $parsed->relationship);
        $this->assertEquals("relationship-column@modifier", $parsed->attribute);
    }

}