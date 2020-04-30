<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigToken extends Model
{
    const UPDATED_AT = null;
    protected $table = 'config_token';
    protected $fillable = ['token', 'api_limit', 'description'];
}
