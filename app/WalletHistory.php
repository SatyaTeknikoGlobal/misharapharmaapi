<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WalletHistory extends Model{
    
    protected $table = 'wallet_transaction';

    protected $guarded = ['id'];


}