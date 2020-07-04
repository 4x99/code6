<?php

namespace App\Models;

class ConfigToken extends ModelBase
{
    const STATUS_UNKNOWN = 0;
    const STATUS_NORMAL = 1;
    const STATUS_ABNORMAL = 2;
    protected $table = 'config_token';
    protected $fillable = ['token', 'description'];
}
