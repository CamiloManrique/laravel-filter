<?php

namespace CamiloManrique\LaravelFilter\Tests;

use CamiloManrique\LaravelFilter\Exceptions\InvalidArgumentException;
use CamiloManrique\LaravelFilter\Exceptions\UnknownColumnException;
use CamiloManrique\LaravelFilter\Tests\Models\Comment;
use CamiloManrique\LaravelFilter\Tests\Models\Country;
use CamiloManrique\LaravelFilter\Tests\Models\PersonalInfo;
use CamiloManrique\LaravelFilter\Tests\Models\Post;
use CamiloManrique\LaravelFilter\Tests\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FiltersTest extends TestCase
{

    protected $country1;
    protected $country2;

    protected function setUp(){
        parent::setUp();

        $this->country1 = factory(Country::class)->create([
            "country" => "United States"
        ]);

        $this->country2 = factory(Country::class)->create([
            "country" => "Canada"
        ]);
    }

    private function createUserWithRelatedModels($number_of_posts = 1, $number_of_comments_per_post = 1, $attributes = [], $personal_info = []){
        $personal_info["country_id"] = $personal_info["country_id"] ?? $this->country1->id;
        $user = factory(User::class)->create($attributes);
        $user->personal_info()->save(factory(PersonalInfo::class)->make($personal_info));
        $user->posts()->saveMany(factory(Post::class, $number_of_posts)->make());
        $user->posts->each(function ($post) use ($number_of_comments_per_post){
            $post->comments()->saveMany(factory(Comment::class, $number_of_comments_per_post)->make());
        });
        return $user;

    }


    /**
     * Assert that InvalidArgument exception is raised if argument is different from Request, array or collection.
     *
     * @return void
     */
    public function testInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        User::filter("InvalidArgument");
    }

    /**
     * Assert that all models are returned when no arguments are passed into the filter.
     *
     * @return void
     */
    public function testNoFiltersQuery(){
        Collection::times(2, function (){
            return $this->createUserWithRelatedModels();
        });

        $users = User::filterAndGet();
        $this->assertCount(2, $users);
    }

    /**
     * Assert that the only model that matches the filtering is returned.
     *
     * @return void
     */
    public function testBasicFiltering(){
        $user1 = $this->createUserWithRelatedModels();
        $this->createUserWithRelatedModels();

        $users = User::filterAndGet(["email" => $user1->email]);
        $this->assertCount(1, $users);
    }

    /**
     * Test relationship filtering and loading. Also test query modificators
     *
     * @return void
     */
    public function testAdvancedFiltering(){
        $name = "John";
        $this->createUserWithRelatedModels(3, 3, [], ["name" => $name]);
        $this->createUserWithRelatedModels(3, 3, [], ["name" => $name]);
        $this->createUserWithRelatedModels(3, 3, [], ["name" => "Sarah"]);

        $users = User::filterAndGet([
            "personal_info@name/like" => $name,
            "relationships" => "posts,comments"
        ]);

        $this->assertCount(2, $users);
        $items = collect($users);
        $items->each(function ($item){
            $post_count = $item->posts->count();
            $comments_count = $item->comments->count();
            $this->assertTrue($post_count === 3 && $comments_count === $post_count * 3);
        });
    }

    /**
     * Test filter array parameters
     * @return void
     */
    public function testArrayParameter(){
        $this->createUserWithRelatedModels(1, 1, [], ["country_id" => $this->country1->id]);
        $this->createUserWithRelatedModels(1, 1, [], ["country_id" => $this->country2->id]);
        $users = User::filterAndGet([
            "personal_info@country_id" => [$this->country1->id, $this->country2->id]
        ]);

        $this->assertCount(2, $users);
    }

    /**
     * Test the sum columns feature
     *
     * @return void
     */
    public function testSum(){
        $user = $this->createUserWithRelatedModels(1, 3);
        $comments = $user->comments;

        $result = $user->comments()->filterAndGet(["sum" => "votes,shares"]);

        $this->assertEquals($comments->sum('votes'), $result->votes);
        $this->assertEquals($comments->sum('shares'), $result->shares);
    }

    /**
     * Test the filter on relationship
     * @return void
     */
    public function testFiltersBasedOnRelationshipQuery(){
        $user = $this->createUserWithRelatedModels();
        $user->posts()->save(new Post(["title" => "TestTitle", "content" => "TestContent"]));

        $response = User::filterAndGet(["posts@title" => "TestTitle"]);

        $this->assertCount(1, $response);
    }

    public function testChainsFilterFromQueryBuilder(){
        $user = $this->createUserWithRelatedModels();

        $response = $user->posts()->filter()->get();
        $this->assertCount(1, $response);

    }


    /**
     * Test pagination
     *
     * @return void
     */
    public function testPagination(){
        Collection::times(5, function (){return $this->createUserWithRelatedModels();});
        $response = User::filterAndGet(["page_size" => 2]);

        $this->assertCount(2, $response);
    }

    /**
     * Test the functionality to filter models based on the count of related models
     */
    public function testFilterBasedOnModelCount(){
        $this->createUserWithRelatedModels(2);
        $this->createUserWithRelatedModels(4);

        $users = User::filterAndGet([
            "posts@model_count" => 2
        ]);

        $this->assertCount(1, $users);
    }

    public function testSortsResults(){
        $this->createUserWithRelatedModels(1, 1, ["email" => "buser@example.com"]);
        $this->createUserWithRelatedModels(1, 1, ["email" => "auser@example.com"]);

        $users = User::filterAndGet(["sort" => "email/desc"]);

        $this->assertEquals("buser@example.com", $users->get(0)->email);
    }

    public function testRaisesExceptionWhenColumnNotExists(){
        if(!$this->isSqlite()){
            $this->expectException(UnknownColumnException::class);
            $this->createUserWithRelatedModels();
            User::filterAndGet(["invalid_column" => "value", "email" => "a"]);
        }
        else{
            $this->markTestSkipped("This cannot be tested with Sqlite driver");
        }
    }

    private function isSqlite(){
        return DB::connection()->getConfig()['driver'] === 'sqlite';
    }

}
