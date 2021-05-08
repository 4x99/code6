<?php

namespace App\Models;

class ConfigWhitelistFile extends ModelBase
{
    const CREATED_AT = null;
    const UPDATED_AT = null;
    protected $table = 'config_whitelist_file';
    protected $fillable = ['value'];
}
