<?php
/**
 * Created by PhpStorm.
 * User: Camilo
 * Date: 3/3/2019
 * Time: 10:29
 */

namespace CamiloManrique\LaravelFilter\Tests\QueryBuilder;


use CamiloManrique\LaravelFilter\QueryBuilder\Relationships;
use CamiloManrique\LaravelFilter\Tests\Helpers\QueryBuilderAssertions;
use CamiloManrique\LaravelFilter\Tests\Models\User;
use CamiloManrique\LaravelFilter\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;

class RelationshipsTest extends TestCase
{
    use QueryBuilderAssertions;

    protected $input;
    protected $relationships;
    /** @var Builder */
    protected $query;

    protected function setUp()
    {
        parent::setUp();
        $this->relationships = ["relationship1", "relationship2"];
        $this->input = collect([
            "relationships" => implode(",", $this->relationships)
        ]);
        $this->query = User::query();

    }

    public function testGetRelationships(){
        $relationships = Relationships::getRelationships($this->input);
        $this->assertArraySubset($relationships, $this->relationships);
    }

    public function testAddRelationships(){
        Relationships::addRelationships($this->query, $this->relationships);
        $this->assertRelationshipsLoaded($this->query, $this->relationships);
    }

}