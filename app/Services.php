<?php
namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class Services extends Model{
    protected $table = 'services';

    protected $guarded = ['id'];

}