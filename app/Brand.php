<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model{
    
    protected $table = 'brand';

    protected $guarded = ['id'];

     public $timestamps = false;

}