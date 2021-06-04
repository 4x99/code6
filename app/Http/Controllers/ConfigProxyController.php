<?php

namespace App\Http\Controllers;

use App\Models\ConfigCommon;
use Illuminate\Http\Request;
use Github\Client;
use Github\HttpClient\Builder;
use Http\Adapter\Guzzle6\Client as GuzzleClient;

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
        try {
            $builder = new Builder(GuzzleClient::createWithConfig([
                'timeout' => 5,
                'proxy' => $request->input('value'),
            ]));
            $client = new Client($builder, 'v3.text-match');
            $client->api('repo')->releases()->latest('4x99', 'code6');
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
