<?php
namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class UserDailyHelp extends Model{
    
    protected $table = 'user_daily_help';

    protected $guarded = ['id'];

    protected $fillable = [];


 
}