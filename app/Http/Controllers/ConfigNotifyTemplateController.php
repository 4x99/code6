<?php

namespace App\Http\Controllers;

use App\Models\ConfigCommon;
use App\Services\NotifyService;
use Illuminate\Http\Request;

class ConfigNotifyTemplateController extends Controller
{
    /**
     * 通知模板
     *
     * @return array
     */
    public function index()
    {
        try {
            $default = [
                'title' => NotifyService::TEMPLATE_DEFAULT_TITLE,
                'content' => implode(PHP_EOL, NotifyService::TEMPLATE_DEFAULT_CONTENT),
            ];
            $data = ConfigCommon::getValue(ConfigCommon::KEY_NOTIFY_TEMPLATE);
            $data = $data ? json_decode($data, true) : $default;
            return ['success' => true, 'data' => $data];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 保存通知模板
     *
     * @param  Request  $request
     * @return array
     */
    public function store(Request $request)
    {
        try {
            $title = $request->input('title');
            $content = $request->input('content');
            $value = json_encode(compact('title', 'content'));
            ConfigCommon::updateOrCreate(['key' => ConfigCommon::KEY_NOTIFY_TEMPLATE], ['value' => $value]);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
