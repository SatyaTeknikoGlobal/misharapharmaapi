<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Flats extends Model{

    protected $table = 'flats';

    protected $guarded = ['id'];



    public function getFlats(){
        return $this->belongsTo('App\Blocks', 'block_id');
    }

}