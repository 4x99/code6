<?php

namespace App\Utils;

class SystemUtil
{
    /**
     * 内存信息
     *
     * @return array
     */
    public static function memory()
    {
        $data = [];
        $info = file_get_contents('/proc/meminfo');
        foreach (explode("\n", $info) as $item) {
            if (preg_match('/^(\w+):\s+(\d+)\skB$/', $item, $matches)) {
                $data[$matches[1]] = $matches[2] * 1024;
            }
        }
        return $data;
    }

    /**
     * 磁盘信息
     *
     * @param  string  $directory
     * @return array
     */
    public static function disk($directory = '.')
    {
        $free = disk_free_space($directory);
        $total = disk_total_space($directory);
        return ['free' => $free, 'total' => $total, 'used' => $total - $free];
    }

    /**
     * 单位转换
     *
     * @param $size
     * @return string
     */
    public static function conv($size)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        for ($n = 0; $size >= 1024 && $n < count($units); $n++) {
            $size /= 1024;
        }
        return sprintf('%s %s', round($size, 2), $units[$n]);
    }
}
