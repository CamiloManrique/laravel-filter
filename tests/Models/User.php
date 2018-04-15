<?php

namespace CamiloManrique\LaravelFilter\Tests\Models;

use CamiloManrique\LaravelFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function personal_info(){
        return $this->hasOne('CamiloManrique\LaravelFilter\Tests\Models\PersonalInfo');
    }

    public function posts(){
        return $this->hasMany(Post::class);
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }

}
