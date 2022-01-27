<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Designer extends Model{
    
    protected $table = 'designers';

    protected $guarded = ['id'];

     public $timestamps = false;

}