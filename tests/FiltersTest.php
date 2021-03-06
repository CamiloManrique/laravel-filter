<?php

namespace CamiloManrique\LaravelFilter\Tests;

use CamiloManrique\LaravelFilter\Tests\Models\Comment;
use CamiloManrique\LaravelFilter\Tests\Models\Country;
use CamiloManrique\LaravelFilter\Tests\Models\PersonalInfo;
use CamiloManrique\LaravelFilter\Tests\Models\Post;
use CamiloManrique\LaravelFilter\Tests\Models\User;

class FiltersTest extends TestCase
{

    protected function setUp(){
        parent::setUp();

        factory(Country::class)->create([
            "country" => "United States"
        ]);
        factory(Country::class)->create([
            "country" => "Canada"
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

        $user4 = factory(User::class)->create([
            "email" => "user4@example.com"
        ]);

        $user4->personal_info()->save(factory(PersonalInfo::class)->make([
            "name" => "Sarah Wilson",
            "country_id" => 2
        ]));

        $users->push($user3);

        $users->each(function ($u){
            $u->posts()->saveMany(factory(Post::class)->times(3)->make());
            $u->comments()->saveMany(factory(Comment::class)->times(3)->make([
                "post_id" => 1,
                "votes" => 1,
                "shares" => 2
            ]));
        });

        $user5 = factory(User::class)->create([
            "email" => "user5@example.com"
        ]);

        $user5->personal_info()->save(factory(PersonalInfo::class)->make([
            "name" => "Caroline Cooper",
            "country_id" => 2
        ]));
        $user5->posts()->saveMany(factory(Post::class)->times(2)->make());

    }

    /**
     * Test factories.
     *
     * @return void
     */
    public function testFactories()
    {
        $this->assertCount(5, User::all());

    }

    /**
     * Assert that InvalidArgument exception is raised if argument is different from Request, array or collection.
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
        $users = User::filterAndGet();
        $this->assertCount(User::count(), $users);
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

        $array_users = collect($users->toArray()['data']);

        $this->assertTrue(
            $array_users->every(function ($user){
                return count($user['posts']) == 3 && count($user['comments']) == 3;
            })
        );
    }

    /**
     * Test filter array parameters
     * @return void
     */
    public function testArrayParameter(){
        $users = User::filterAndGet([
            "personal_info@country_id" => [1, 2]
        ]);

        $this->assertCount(User::count(), $users);
    }

    /**
     * Test the sum columns feature
     *
     * @return void
     */
    public function testSum(){
        $result = Comment::filterAndGet([
            "sum" => "votes,shares",
            "user_id" => 1
        ]);

        $this->assertNotNull($result->votes);
        $this->assertNotNull($result->shares);

        $this->assertEquals(3, $result->votes);
        $this->assertEquals(6, $result->shares);
    }

    /**
     * Test the filter on relationship
     * @return void
     */
    public function testRelationshipQuery(){
        $user = User::all()->first();
        $user->posts()->save(new Post(["title" => "TestTitle", "content" => "TestContent"]));

        $response = $user->posts()->filter(["title" => "TestTitle"])->get();
        $this->assertCount(1, $response);
    }


    /**
     * Test pagination
     *
     * @return void
     */
    public function testPagination(){
        $comments = Comment::filterAndGet([
            "user_id" => 1,
            "page_size" => 2
        ]);

        $this->assertCount(2, $comments);
    }

    /**
     * Test that the filter methods can be chained with the Eloquent relationship methods.
     */
    public function testFilterFromEloquentRelationship(){

        $user = User::all()->first();
        $user->posts()->save(new Post(["title" => "TestTitle", "content" => "TestContent"]));

        $user = User::find(1);
        $response = $user->posts()->filterAndGet(["title" => "TestTitle"]);

        $this->assertCount(1, $response);

    }

    /**
     * Test the functionality to filter models based on the count of related models
     */
    public function testFilterBasedOnModelCount(){

        $users = User::filterAndGet([
            "posts@model_count" => 2
        ]);

        $this->assertCount(1, $users);


    }

}
