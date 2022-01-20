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
            $detail = ConfigCommon::getValue(ConfigCommon::KEY_NOTIFY_DETAIL);
            $limit = ConfigCommon::getValue(ConfigCommon::KEY_NOTIFY_DETAIL_LIMIT);

            $data['title'] = $config['title'] ?? NotifyService::TEMPLATE_DEFAULT_TITLE;
            $data['content'] = $config['content'] ?? NotifyService::TEMPLATE_DEFAULT_CONTENT;
            $data['detail'] = $detail ?? NotifyService::TEMPLATE_DEFAULT_DETAIL;
            $data['limit'] = $limit ?? NotifyService::TEMPLATE_DEFAULT_LIMIT;
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
            $detail = $request->input('detail');
            $limit = $request->input('limit');
            $data = [
                ConfigCommon::KEY_NOTIFY_TEMPLATE => $value,
                ConfigCommon::KEY_NOTIFY_DETAIL => $detail,
                ConfigCommon::KEY_NOTIFY_DETAIL_LIMIT => $limit
            ];
            foreach ($data as $key => $value) {
                ConfigCommon::updateOrCreate(
                    ['key' => $key], ['value' => $value]
                );
            }
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
