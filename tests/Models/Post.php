<?php

namespace CamiloManrique\LaravelFilter\Tests\Models;

use CamiloManrique\LaravelFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

    protected $fillable = ["title", "content"];

    public function user(){
        return $this->belongsTo('CamiloManrique\LaravelFilter\Tests\Models\User');
    }

    public function comments(){
        return $this->hasMany('CamiloManrique\LaravelFilter\Tests\Models\Comment');
    }
}
