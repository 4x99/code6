<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class NotifyService
{
    const EMAIL_TITLE = '码小六消息通知';
    const URL_TELEGRAM = 'https://api.telegram.org/bot%s/sendMessage?chat_id=%s&text=%s';

    /**
     * 邮件
     *
     * @param $content
     * @param $config
     * @return array|bool[]
     */
    public function email($content, $config)
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
            Mail::send('email.index', compact('content'), function ($message) use ($config) {
                $message->from($config['username']);
                $message->subject(self::EMAIL_TITLE);
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
     * @param $content
     * @param $config
     * @return array
     */
    public function webhook($content, $config)
    {
        try {
            $data = compact('content');
            $params = $config['params'] ?? [];
            foreach (explode(PHP_EOL, $params) as $param) {
                list($k, $v) = explode(':', $param);
                $data[trim($k)] = trim($v);
            }

            $ch = curl_init($config['webhook']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array_filter($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, explode(PHP_EOL, $config['headers']));
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new \Exception(curl_error($ch));
            }
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
