<?php

namespace App\Http\Controllers;

use App\Models\ConfigCommon;
use Illuminate\Http\Request;

class ConfigWhitelistFileController extends Controller
{
    /**
     * 文件名列表
     *
     * @return array
     */
    public function index()
    {
        try {
            $data = ConfigCommon::getValue(ConfigCommon::KEY_WHITELIST_FILE);
            $data = implode("\n", json_decode($data));
            return ['success' => true, 'data' => $data];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 保存文件名列表
     *
     * @param  Request  $request
     * @return array
     */
    public function store(Request $request)
    {
        try {
            $value = explode("\n", $request->input('value'));
            $value = json_encode(array_values(array_filter(array_unique($value))));
            ConfigCommon::updateOrCreate(['key' => ConfigCommon::KEY_WHITELIST_FILE], ['value' => $value]);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
