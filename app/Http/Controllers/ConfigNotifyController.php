<?php

namespace App\Http\Controllers;

use App\Models\ConfigNotify;
use App\Services\NotifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

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
                'type' => ['required', Rule::in(ConfigNotify::TYPE)],
                'enable' => ['required'],
            ]);
            $commonField = ['enable', 'interval_min', 'start_time', 'end_time'];
            $data = $request->only($commonField);
            $data['value'] = json_encode($request->except(array_merge($commonField, ['type'])));
            $data = ConfigNotify::updateOrCreate(['type' => $request->input('type')], $data);
            return ['success' => true, 'data' => $data];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 通知测试
     *
     * @param  Request  $request
     * @return mixed
     */
    public function test(Request $request)
    {
        $config = $request->all();
        $type = $config['type'];

        $count = 'xxx';
        $etime = now()->toDateTimeString();
        $stiem = now()->subHour()->toDateTimeString();

        $service = new NotifyService();
        $tpl = $service->getTemplate($type, $stiem, $etime, $count);
        return $service->$type($tpl['title'], $tpl['content'], $config);
    }

    /**
     * 读取配置
     *
     * @return mixed
     */
    private function getConfig()
    {
        $config = ConfigNotify::get()->keyBy('type')->toArray();
        foreach ($config as &$value) {
            $value['value'] = json_decode($value['value'], true);
        }
        return $config;
    }
}
