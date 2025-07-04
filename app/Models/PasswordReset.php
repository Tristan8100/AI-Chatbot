<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets';

    protected $fillable = ['email', 'code', 'token', 'created_at'];
    public $timestamps = true;
}
