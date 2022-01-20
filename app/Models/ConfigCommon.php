<?php

namespace App\Models;

class ConfigCommon extends ModelBase
{
    const KEY_PROXY = 'proxy';
    const KEY_WHITELIST_FILE = 'whitelist_file';
    const KEY_NOTIFY_TEMPLATE = 'notify_template';
    const KEY_NOTIFY_DETAIL = 'notify_detail';
    const KEY_NOTIFY_DETAIL_LIMIT = 'notify_detail_limit';
    const CREATED_AT = null;
    const UPDATED_AT = null;
    protected $table = 'config_common';
    protected $fillable = ['key', 'value'];

    /**
     * @param $key
     * @return mixed
     */
    public static function getValue($key)
    {
        return self::where('key', $key)->value('value');
    }
}
