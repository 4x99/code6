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
            return [
                'success' => true,
                'data' => ConfigCommon::where('key', ConfigCommon::KEY_WHITELIST_FILE)->get()->implode('value', "\n"),
            ];
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
            $data = [];
            $values = explode("\n", $request->input('value'));
            $values = array_filter(array_unique($values));
            foreach ($values as $value) {
                $data[] = [
                    'value' => $value,
                    'key' => ConfigCommon::KEY_WHITELIST_FILE,
                ];
            }
            ConfigCommon::where('key', ConfigCommon::KEY_WHITELIST_FILE)->delete();
            ConfigCommon::insert($data);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
