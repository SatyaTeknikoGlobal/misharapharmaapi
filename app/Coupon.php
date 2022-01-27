<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model{
    
    protected $table = 'coupons';

    protected $guarded = ['id'];

     public $timestamps = false;

}