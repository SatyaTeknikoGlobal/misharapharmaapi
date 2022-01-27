<?php
namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class State extends Model{
    protected $table = 'states';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $fillable = 
    [
        'iso ',
        'name',
        'nicename '
    ];


    public function country(){
    	return $this->belongsTo('App\Country');
    }

}