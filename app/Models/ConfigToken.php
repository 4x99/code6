<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigToken extends Model
{
    protected $table = 'config_token';
    protected $fillable = ['token', 'description'];
}
