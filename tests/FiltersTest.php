<?php

namespace CamiloManrique\Filter\Tests;

use CamiloManrique\Filter\Tests\Models\Country;
use CamiloManrique\Filter\Tests\Models\PersonalInfo;
use CamiloManrique\Filter\Tests\Models\Post;
use CamiloManrique\Filter\Tests\Models\User;

class FiltersTest extends TestCase
{

    protected function setUp(){
        parent::setUp();

        factory(Country::class)->create([
            "country" => "United States"
        ]);

        $users = collect();

        $user1 = factory(User::class)->create([
            "email" => "user1@example.com"
        ]);
        $user1->personal_info()->save(factory(PersonalInfo::class)->make([
            "name" => "John Mayers",
            "country_id" => 1
        ]));

        $users->push($user1);

        $user2 = factory(User::class)->create([
            "email" => "user2@example.com"
        ]);
        $user2->personal_info()->save(factory(PersonalInfo::class)->make([
            "name" => "John Connor",
            "country_id" => 1
        ]));

        $users->push($user2);

        $user3 = factory(User::class)->create([
            "email" => "user3@example.com"
        ]);
        $user3->personal_info()->save(factory(PersonalInfo::class)->make([
            "name" => "Maya Sanders",
            "country_id" => 1
        ]));

        $users->push($user3);

//        $users->each(function ($u){
//            $u->posts()->create(factory(Post::class, 3)->make());
//        });

    }

    /**
     * Test factories.
     *
     * @return void
     */
    public function testFactories()
    {
        $this->assertCount(3, User::all());

    }

    /**
     * Assert that InvalidArgument exception is raised if argument is different from Request or array.
     *
     * @return void
     */
    public function testInvalidArgument()
    {
        $this->expectException(\InvalidArgumentException::class);
        User::filter("InvalidArgument");
    }

    /**
     * Assert that all models are returned when no arguments are passed into the filter.
     *
     * @return void
     */
    public function testNoFiltersQuery(){
        $users = User::filterAndGet([]);
        $this->assertCount(3, $users);
    }

    /**
     * Assert that the only model that matches the filtering is returned.
     *
     * @return void
     */
    public function testBasicFiltering(){
        $users = User::filterAndGet(["email" => "user1@example.com"]);
        $this->assertCount(1, $users);
        $this->assertNotFalse(
            $users->search(function ($item, $key){
                return $item->email == 'user1@example.com';
            })
        );
    }

    /**
     * Test relationship filtering and loading. Also test query modificators
     *
     * @return void
     */
    public function testAdvancedFiltering(){
        $users = User::filterAndGet([
            "personal_info@name/like" => "John",
            "relationships" => "posts,comments"
        ]);

        $this->assertCount(2, $users);

    }




}
