<?php

namespace App\Http\Controllers;

use App\Models\ConfigNotify;
use Illuminate\Http\Request;

class ConfigNotifyController extends Controller
{

    public function view()
    {
        $data = [
            'title' => '通知配置',
            'config' => $this->getConfig(),
        ];
        return view('configNotify.index', $data);
    }

    /**
     * 更新通知配置
     *
     * @param  Request  $request
     * @return array
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'type' => ['required', 'string'],
                'value' => ['required', 'string'],
                'enable' => ['required', 'integer'],
            ]);
            $params = $request->all(['type', 'value', 'enable']);
            if ($configNotify = ConfigNotify::whereType($params['type'])->first()) {
                $success = (bool) $configNotify->update($params);
            } else {
                $configNotify = ConfigNotify::create($params);
                $success = (bool) $configNotify;
            }
            return ['success' => $success, 'data' => $configNotify];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 获取配置
     *
     * @return mixed
     */
    private function getConfig()
    {
        $config = ConfigNotify::get()->keyBy('type')->toArray();
        foreach (ConfigNotify::TYPE as $type) {
            $typeConfig = $config[$type] ?? ['enable' => 0, 'value' => ''];
            $typeConfig['value'] = json_decode($typeConfig['value'], true) ?: [];
            foreach ($typeConfig['value'] as &$item) {
                $item = str_replace("\n", "\\n", $item);// 防止多行报错
            }
            $config[$type] = $typeConfig;
        }
        return $config;
    }

}
