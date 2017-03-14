<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Identity extends Model
{
    //
    protected $table = 'identity';
    protected $guarded =  ['id'];
    /*public function workplaces()
    {
        return $this->hasMany('App\Model\WorkPlace');
    }*/
}
