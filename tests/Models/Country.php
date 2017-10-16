<?php

namespace CamiloManrique\Filter\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    public $timestamps = false;

    public function personal_info(){
        return $this->hasMany('CamiloManrique\Filter\Tests\Models\PersonalInfo');
    }
}
