<?php
namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class ServiceDetail extends Model{
    
    protected $table = 'service_details';

    protected $guarded = ['id'];

    protected $fillable = [];


 
}