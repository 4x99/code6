<?php

namespace App\Models;

class User extends ModelBase
{
    protected $table = 'user';
    protected $fillable = ['email', 'password'];
    protected $hidden = ['password'];
}
