<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    //
    protected $guarded = ['id'];
    protected $table = 'product_cart';
}
