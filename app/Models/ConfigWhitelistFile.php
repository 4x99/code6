<?php

namespace App\Models;

class ConfigWhitelistFile extends ModelBase
{
    const PATH = 'app/whitelist-file.conf';

    /**
     * @return false|string
     */
    public static function get()
    {
        $path = storage_path(self::PATH);
        return file_exists($path) ? file_get_contents($path) : '';
    }

    /**
     * @param $value
     */
    public static function put($value)
    {
        $fp = fopen(storage_path(self::PATH), 'w');
        fwrite($fp, $value);
        fclose($fp);
    }

    /**
     * @return array
     */
    public static function getConfig()
    {
        $value = explode("\n", self::get());
        return array_unique(array_filter($value));
    }
}
