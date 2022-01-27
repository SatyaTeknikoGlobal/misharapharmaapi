<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserOtp extends Model
{


	protected $table = 'user_otp';
    protected $guarded = ['id'];
}
