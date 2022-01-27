<?php
namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class Society extends Model{
    protected $table = 'societies';

    protected $guarded = ['id'];

    protected $fillable = [];


 
}