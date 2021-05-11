<?php

namespace App\Http\Controllers;

use App\Models\ConfigWhitelistFile;
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
            return ['success' => true, 'data' => ConfigWhitelistFile::all()->implode('value', "\n")];
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
            $values = array_filter(array_unique(explode("\n", $request->input('value'))));
            foreach ($values as $value) {
                $data[] = ['value' => $value];
            }
            ConfigWhitelistFile::truncate();
            ConfigWhitelistFile::insert($data);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
