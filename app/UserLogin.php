<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLogin extends Model
{
    
    protected $table = 'user_logins';

    protected $guarded = ['id'];
}
