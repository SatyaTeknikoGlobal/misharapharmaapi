<?php
namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class SocietyUser extends Model{
    protected $table = 'societyusers';

    protected $guarded = ['id'];

    protected $fillable = [];


 
}