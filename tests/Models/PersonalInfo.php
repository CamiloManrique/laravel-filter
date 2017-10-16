<?php

namespace CamiloManrique\Filter\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalInfo extends Model
{
    protected $table = "personal_info";

    public function user(){
        return $this->belongsTo('CamiloManrique\Filter\Tests\Models\User');
    }
}
