<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model{
    
    protected $table = 'products';

    protected $guarded = ['id'];

     public function verients(){
        return $this->hasMany('\App\Verient','product_id')->select('id','name','price','retail_price');
    }

    public function get_verients(){

    }

}