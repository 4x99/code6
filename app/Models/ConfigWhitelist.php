<?php

namespace App\Models;

class ConfigWhitelist extends ModelBase
{
    const CREATED_AT = null;
    const UPDATED_AT = null;
    const FILE_CONFIG_PATH = 'whitelist-file.txt';
    protected $table = 'config_whitelist';
    protected $fillable = ['value'];


    public static function getFileConfig()
    {
        $filePath = storage_path(self::FILE_CONFIG_PATH);
        return file_exists($filePath) ? file_get_contents($filePath) : '';
    }
}
