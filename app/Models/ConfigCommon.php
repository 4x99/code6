<?php

namespace App\Models;

class ConfigCommon extends ModelBase
{
    const KEY_PROXY = 'proxy';
    const KEY_WHITELIST_FILE = 'whitelist_file';
    const CREATED_AT = null;
    const UPDATED_AT = null;
    protected $table = 'config_common';
    protected $fillable = ['key', 'value'];
}
