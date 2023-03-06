<?php

namespace App\Services;

use App\Models\ConfigCommon;
use App\Models\ConfigNotify;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
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
            'host' => $config['host'],
            'port' => $config['port'] ?? 465,
            'username' => $config['username'],
            'password' => $config['password'],
            'encryption' => $config['encryption'] ?? 'ssl',
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
        } catch (Exception $e) {
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

            // 设置头部信息
            foreach (explode(PHP_EOL, $config['headers']) as $header) {
                list($key, $val) = explode(':', $header);
                $headers[trim($key)] = trim($val);
            }

            // 设置请求参数
            foreach (explode(PHP_EOL, $config['params']) as $param) {
                list($key, $val) = explode(':', $param);
                $val = str_replace(['{{title}}', '{{content}}'], [$title, $content], trim($val));
                Arr::set($formParams, trim($key), trim($val));
            }

            $response = $this->post($config['webhook'], ['headers' => $headers, 'form_params' => $formParams]);
            return ['success' => true, 'data' => $response];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Telegram
     *
     * @param $title
     * @param $content
     * @param $config
     * @return array
     */
    public function telegram($title, $content, $config)
    {
        try {
            $content = $title.PHP_EOL.$content;
            $url = sprintf(self::URL_TELEGRAM, $config['token'], $config['chat_id'], urlencode($content));
            $response = $this->post($url);
            $response = json_decode($response, true);
            if ($response['ok'] != true) {
                throw new Exception($response['description']);
            }
            return ['success' => true, 'data' => $response];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 飞书
     *
     * @param $title
     * @param $content
     * @param $config
     * @return array
     */
    public function feishu($title, $content, $config)
    {
        $data = [
            'msg_type' => 'text',
            'content' => ['text' => $title.PHP_EOL.$content],
        ];

        try {
            $url = $config['webhook'];
            $response = $this->post($url, ['json' => $data]);
            $response = json_decode($response, true);
            if ($response['code'] > 0) {
                throw new Exception($response['msg']);
            }
            return ['success' => true, 'data' => $response];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 钉钉
     *
     * @param $title
     * @param $content
     * @param $config
     * @return array
     */
    public function dingTalk($title, $content, $config)
    {
        $data = [
            'msgtype' => 'text',
            'text' => ['content' => $title.PHP_EOL.$content],
        ];

        try {
            $url = $config['webhook'];
            $response = $this->post($url, ['json' => $data]);
            $response = json_decode($response, true);
            if ($response['errcode'] != 0) {
                throw new Exception($response['errmsg']);
            }
            return ['success' => true, 'data' => $response];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 企业微信
     *
     * @param $title
     * @param $content
     * @param $config
     * @return array
     */
    public function workWechat($title, $content, $config)
    {
        $data = [
            'msgtype' => 'text',
            'text' => ['content' => $title.PHP_EOL.$content],
        ];

        try {
            $url = $config['webhook'];
            $response = $this->post($url, ['json' => $data]);
            $response = json_decode($response, true);
            if ($response['errcode'] != 0) {
                throw new Exception($response['errmsg']);
            }
            return ['success' => true, 'data' => $response];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 获取通知模板消息
     *
     * @param $type
     * @param $stime
     * @param $etime
     * @param $count
     * @return array
     */
    public function getTemplate($type, $stime, $etime, $count)
    {
        $config = ConfigCommon::getValue(ConfigCommon::KEY_NOTIFY_TEMPLATE);
        $config = json_decode($config, true);
        $title = $config['title'] ?? self::TEMPLATE_DEFAULT_TITLE;
        $content = $config['content'] ?? self::TEMPLATE_DEFAULT_CONTENT;
        if ($type === ConfigNotify::TYPE_EMAIL) {
            $content = str_replace(PHP_EOL, '<br/>', $content);
        }
        $content = str_replace(['{{stime}}', '{{etime}}', '{{count}}'], [$stime, $etime, $count], $content);
        return compact('title', 'content');
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
