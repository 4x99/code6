<?php

namespace App\Models;

class ConfigToken extends ModelBase
{
    protected $table = 'config_token';
    protected $fillable = ['token', 'description'];
}
