<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigWhitelist extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;
    protected $table = 'config_whitelist';
    protected $fillable = ['value'];
}
