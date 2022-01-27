<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GuardLogin extends Model
{
    
    protected $table = 'guard_logins';

    protected $guarded = ['id'];
}
