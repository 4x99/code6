<?php

namespace App\Http\Controllers;

use App\Models\ConfigCommon;
use App\Services\NotifyService;
use Exception;
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
            $config = ConfigCommon::getValue(ConfigCommon::KEY_NOTIFY_TEMPLATE);
            $config = json_decode($config, true);
            $data['title'] = $config['title'] ?? NotifyService::TEMPLATE_DEFAULT_TITLE;
            $data['content'] = $config['content'] ?? NotifyService::TEMPLATE_DEFAULT_CONTENT;
            return ['success' => true, 'data' => $data];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 更新通知模板
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
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
