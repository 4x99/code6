<?php

namespace App\Models;

class ConfigWhitelist extends ModelBase
{
    const CREATED_AT = null;
    const UPDATED_AT = null;
    protected $table = 'config_whitelist';
    protected $fillable = ['value'];


    public static function getFileConfig()
    {
        $filePath = storage_path('whitelist.txt');
        return file_exists($filePath) ? file_get_contents($filePath) : '';
    }
}
