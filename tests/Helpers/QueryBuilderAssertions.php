<?php
/**
 * Created by PhpStorm.
 * User: Camilo
 * Date: 3/3/2019
 * Time: 09:31
 */

namespace CamiloManrique\LaravelFilter\Tests\Helpers;


use Illuminate\Database\Eloquent\Builder;

trait QueryBuilderAssertions
{

    /**
     *
     * @param Builder $query
     * @param string $columnArray
     */
    public function assertBasicWhereClauseHasColumn(Builder $query, string $column){
        $where_clauses = collect($query->getQuery()->wheres);
        $has_column = $where_clauses->contains("column", $column);
        $this->assertTrue($has_column);
    }

    /**
     * @param Builder $query
     * @param array $columns
     */
    public function assertBasicWhereClauseHasColumns(Builder $query, array $columns){
        foreach ($columns as $column){
            $this->assertBasicWhereClauseHasColumn($query, $column);
        }
    }

    public function assertRelationshipLoaded(Builder $query, string $relationship){
        $eager_loads = collect($query->getEagerLoads());
        $has_relationship = $eager_loads->has($relationship);
        $this->assertTrue($has_relationship);
    }

    public function assertRelationshipsLoaded(Builder $query, array $relationships){
        foreach ($relationships as $relationship){
            $this->assertRelationshipLoaded($query, $relationship);
        }
    }

    public function assertQuerySorting(Builder $query, string $column, string $direction){
        $orders = collect($query->getQuery()->orders);
        $has_desired_column = $orders->contains("column", $column);
        $has_desired_direction = $orders->contains("direction", $direction);
        $this->assertTrue($has_desired_column && $has_desired_direction);
    }


}