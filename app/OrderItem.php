<?php
namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model{
    
    protected $table = 'order_items';

    protected $guarded = ['id'];

    protected $fillable = [];


 
}