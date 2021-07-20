<?php

namespace App\Services;

use App\Models\ConfigCommon;
use App\Models\ConfigNotify;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class NotifyService
{
    const TEMPLATE_DEFAULT_TITLE = '码小六消息通知';
    const TEMPLATE_DEFAULT_CONTENT = "开始时间：{{stime}}\n结束时间：{{etime}}\n本时段共有 {{count}} 条未审记录";
    const URL_TELEGRAM = 'https://api.telegram.org/bot%s/sendMessage?chat_id=%s&text=%s';

    /**
     * 邮件
     *
     * @param $title
     * @param $content
     * @param $config
     * @return array|bool[]
     */
    public function email($title, $content, $config)
    {
        Config::set('mail', [
            'driver' => 'smtp',
            'encryption' => 'ssl',
            'host' => $config['host'],
            'port' => $config['port'] ?? 465,
            'username' => $config['username'],
            'password' => $config['password'],
        ]);

        try {
            Mail::send('email.index', compact('content'), function ($message) use ($config, $title) {
                $message->from($config['username']);
                $message->subject($title);
                foreach (explode(PHP_EOL, $config['to']) as $email) {
                    if ($email = trim($email)) {
                        $message->to($email);
                    }
                }
            });
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Webhook
     *
     * @param $title
     * @param $content
     * @param $config
     * @return array
     */
    public function webhook($title, $content, $config)
    {
        try {
            $headers = $formParams = [];
            $config['headers'] = $config['headers'] ?? '';
            $config['params'] = $config['params'] ?? '';

            // 请求头部转数组
            foreach (explode(PHP_EOL, $config['headers']) as $header) {
                list($key, $val) = explode(':', $header);
                $headers[trim($key)] = trim($val);
            }

            // 请求参数转数组
            foreach (explode(PHP_EOL, $config['params']) as $param) {
                list($key, $val) = explode(':', $param);
                $val = trim($val);
                $val = $val == '{{title}}' ? $title : ($val == '{{content}}' ? $content : trim($val));
                $this->nestedVarToArr($formParams, trim($key), trim($val));
            }

            $response = $this->post($config['webhook'], ['headers' => $headers, 'form_params' => $formParams]);
            return ['success' => true, 'data' => $response];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Telegram
     *
     * @param $content
     * @param $config
     * @return array
     */
    public function telegram($content, $config)
    {
        try {
            $url = sprintf(self::URL_TELEGRAM, $config['token'], $config['chat_id'], urlencode($content));
            $response = $this->post($url);
            $response = json_decode($response, true);
            if ($response['ok'] != true) {
                throw new \Exception($response['description']);
            }
            return ['success' => true, 'data' => $response];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 飞书
     *
     * @param $content
     * @param $config
     * @return array
     */
    public function feishu($content, $config)
    {
        $data = [
            'msg_type' => 'text',
            'content' => ['text' => $content],
        ];

        try {
            $url = $config['webhook'];
            $response = $this->post($url, ['json' => $data]);
            $response = json_decode($response, true);
            if ($response['code'] > 0) {
                throw new \Exception($response['msg']);
            }
            return ['success' => true, 'data' => $response];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 钉钉
     *
     * @param $content
     * @param $config
     * @return array
     */
    public function dingTalk($content, $config)
    {
        $data = [
            'msgtype' => 'text',
            'text' => ['content' => $content],
        ];

        try {
            $url = $config['webhook'];
            $response = $this->post($url, ['json' => $data]);
            $response = json_decode($response, true);
            if ($response['errcode'] != 0) {
                throw new \Exception($response['errmsg']);
            }
            return ['success' => true, 'data' => $response];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 企业微信
     *
     * @param $content
     * @param $config
     * @return array
     */
    public function workWechat($content, $config)
    {
        $data = [
            'msgtype' => 'text',
            'text' => ['content' => $content],
        ];

        try {
            $url = $config['webhook'];
            $response = $this->post($url, ['json' => $data]);
            $response = json_decode($response, true);
            if ($response['errcode'] != 0) {
                throw new \Exception($response['errmsg']);
            }
            return ['success' => true, 'data' => $response];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 获取通知模板消息
     *
     * @param $type
     * @param $stiem
     * @param $etime
     * @param $count
     * @return array
     */
    public function getTemplateNotification($type, $stiem, $etime, $count)
    {
        $template = ConfigCommon::getValue(ConfigCommon::KEY_NOTIFY_TEMPLATE);
        $template = json_decode($template, true);
        $title = $template['title'] ?? self::TEMPLATE_DEFAULT_TITLE;
        $content = $template['content'] ?? self::TEMPLATE_DEFAULT_CONTENT;

        $content = $title.PHP_EOL.$content;
        $content = str_replace(PHP_EOL, $type === ConfigNotify::TYPE_EMAIL ? '<br/><br/>' : "\n\n", $content);

        $content = str_replace('{{stime}}', $stiem, $content);
        $content = str_replace('{{etime}}', $etime, $content);
        $content = str_replace('{{count}}', $count, $content);

        return compact('title', 'content');
    }

    /**
     * 嵌套变量转数组
     *
     * @param $arr
     * @param $key
     * @param $val
     * @param  string  $separator
     */
    private function nestedVarToArr(&$arr, $key, $val, $separator = '.')
    {
        $pieces = explode($separator, $key);
        foreach ($pieces as $piece) {
            $arr = &$arr[$piece];
        }
        $arr = $val;
    }

    /**
     * POST
     *
     * @param $url
     * @param  array  $options
     * @return string
     */
    private function post($url, $options = [])
    {
        $options['verify'] = false;
        $client = new Client(['timeout' => 10, 'connect_timeout' => 10]);
        $response = $client->post($url, $options);
        return $response->getBody()->getContents();
    }
}
