<?php

namespace App\Http\Controllers;

use App\Models\ConfigCommon;
use App\Services\GitHubService;
use Illuminate\Http\Request;

class ConfigProxyController extends Controller
{
    /**
     * 代理配置
     *
     * @return array
     */
    public function index()
    {
        try {
            return ['success' => true, 'data' => ConfigCommon::getValue(ConfigCommon::KEY_PROXY)];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 更新代理配置
     *
     * @param  Request  $request
     * @return array
     */
    public function store(Request $request)
    {
        try {
            ConfigCommon::updateOrCreate(['key' => ConfigCommon::KEY_PROXY], $request->all('value'));
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 代理测试
     *
     * @param  Request  $request
     * @return array
     */
    public function test(Request $request)
    {
        $service = new GitHubService();
        if ($service->testProxy($request->input('value'))) {
            return ['success' => true];
        } else {
            return ['success' => false];
        }
    }
}
