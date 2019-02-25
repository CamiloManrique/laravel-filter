<?php
/**
 * Created by PhpStorm.
 * User: Camilo
 * Date: 26/2/2019
 * Time: 23:01
 */

namespace CamiloManrique\LaravelFilter\Tests\QueryBuilder;


use CamiloManrique\LaravelFilter\QueryBuilder\ColumnFilters;
use CamiloManrique\LaravelFilter\Tests\Helpers\QueryBuilderAssertions;
use CamiloManrique\LaravelFilter\Tests\Models\User;
use CamiloManrique\LaravelFilter\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ColumnFiltersTest extends TestCase
{
    use QueryBuilderAssertions;

    /** @var Collection */
    protected $columnFilters;

    /** @var Builder */
    protected $query;

    protected function setUp()
    {
        parent::setUp();
        $this->columnFilters = collect([
            "column1" => "value1",
            "column2" => "value2"
        ]);
        $this->query = User::query();
    }

    public function testGetFilters(){
        $columns = ColumnFilters::getFilters($this->columnFilters);
        $this->assertArraySubset($columns, $this->columnFilters->all());
    }

    public function testExcludesKeywordsFromFilters(){
        $columnFilters = collect($this->columnFilters->all());
        $columnFilters->put("page_size", 30);
        $columns = ColumnFilters::getFilters($columnFilters);
        $this->assertArraySubset($columns, $this->columnFilters->all());
    }

    public function testAddFiltersToQuery(){
        $columns = ColumnFilters::getFilters($this->columnFilters);
        ColumnFilters::addFilters($this->query, $columns);
        $this->assertBasicWhereClauseHasColumns($this->query, $this->columnFilters->keys()->all());
    }

    public function testAddsFiltersToQueryWithArrayParameter(){
        $columnFilters = collect([
            "array_filter" => ["value1", "value2"]
        ]);
        ColumnFilters::addFilters($this->query, $columnFilters->all());
        $this->assertBasicWhereClauseHasColumns($this->query, $columnFilters->keys()->all());
    }

}