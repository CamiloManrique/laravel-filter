<?php

namespace CamiloManrique\Filter\Tests\Models;

use CamiloManrique\Filter\Filterable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Filterable;

    public function user(){
        return $this->belongsTo('CamiloManrique\Filter\Tests\Models\User');
    }
}
