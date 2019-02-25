<?php
/**
 * Created by PhpStorm.
 * User: Camilo
 * Date: 3/3/2019
 * Time: 10:45
 */

namespace CamiloManrique\LaravelFilter\Tests\QueryBuilder;


use CamiloManrique\LaravelFilter\QueryBuilder\Exceptions\InvalidSortingFormatException;
use CamiloManrique\LaravelFilter\QueryBuilder\Sorting;
use CamiloManrique\LaravelFilter\Tests\Helpers\QueryBuilderAssertions;
use CamiloManrique\LaravelFilter\Tests\Models\User;
use CamiloManrique\LaravelFilter\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SortingTest extends TestCase
{
    use QueryBuilderAssertions;

    /** @var Collection */
    protected $input;

    /** @var array */
    protected $sorting;

    /** @var Builder */
    protected $query;

    protected function setUp()
    {
        parent::setUp();
        $this->sorting = ["column1", "asc"];
        $this->input = collect([
            "sort" => implode("/", $this->sorting)
        ]);
        $this->query = User::query();

    }

    public function testGetSorting(){
        $sorting = Sorting::getSorting($this->input);
        $this->assertArraySubset($this->sorting, $sorting);
    }

    public function testRaisesExceptionIfSortingHasInvalidFormat(){
        $input = collect([
            "sort" => "column1@asc"
        ]);
        $this->expectException(InvalidSortingFormatException::class);
        Sorting::getSorting($input);
    }

    public function testSortResult(){
        $query = Sorting::sortResult($this->query, $this->sorting);
        $this->assertQuerySorting($query, $this->sorting[0], $this->sorting[1]);
    }

}