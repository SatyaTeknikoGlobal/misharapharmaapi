<?php
namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class UserVehicle extends Model{
    
    protected $table = 'user_vehicle';

    protected $guarded = ['id'];

    protected $fillable = [];


 
}