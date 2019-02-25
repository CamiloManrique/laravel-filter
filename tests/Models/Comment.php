<?php

namespace CamiloManrique\LaravelFilter\Tests\Models;

use CamiloManrique\LaravelFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{

    public function post(){
        return $this->belongsTo('CamiloManrique\LaravelFilter\Tests\Models\Post');
    }
}
