<?php

namespace App\Services;

use App\Models\ConfigNotify;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyService
{
    const LOG_CHANNEL = 'code6:notify';
    const EMAIL_FROM_NAME = 'code6';
    const EMAIL_TITLE = '代码泄露通知';
    const URL_TELEGRAM = 'https://api.telegram.org/bot%s/sendMessage?chat_id=%s&text=%s';

    private $log;

    public function __construct()
    {
        $this->log = Log::channel(self::LOG_CHANNEL);
    }

    /**
     * 邮件
     *
     * @param  string  $content
     * @param  ConfigNotify  $configNotify
     */
    public function email($content, $configNotify)
    {
        $config = json_decode($configNotify->value, true);
        // 邮件配置
        Config::set('mail', [
            'driver' => 'smtp',
            'encryption' => 'ssl',
            'host' => $config['host'],
            'port' => $config['port'],
            'username' => $config['username'],
            'password' => $config['password'],
        ]);

        try {
            Mail::send('email.index', compact('content'), function ($message) use ($content, $config) {
                $message->from($config['username'], self::EMAIL_FROM_NAME)->subject(self::EMAIL_TITLE);
                $toEmails = array_values(array_filter(explode("\n", $config['to'])));
                foreach ($toEmails as $toEmail) {
                    $message->to($toEmail);
                }
            });
            $this->log->info('Send email success', [$content]);
        } catch (\Exception $exception) {
            $this->log->error('Send email fail', [$content, $exception->getMessage()]);
        }
    }

    /**
     * 钉钉
     *
     * @param  string  $content
     * @param  ConfigNotify  $configNotify
     */
    public function dingTalk($content, $configNotify)
    {
        $config = json_decode($configNotify->value, true);
        $data = [
            'msgtype' => 'text',
            'text' => [
                'content' => $content,
            ],
        ];

        try {
            $url = $config['webhook'];
            $response = $this->post($url, ['json' => $data]);
            $response = json_decode($response, true);
            if ($response['errcode'] != 0) {
                throw new \Exception($response['errmsg']);
            }
            $this->log->info('Send dingTalk success', [$content, $response]);
        } catch (\Exception $exception) {
            $this->log->error('Send dingTalk fail', [$content, $exception->getMessage()]);
        }
    }

    /**
     * 企业微信
     *
     * @param  string  $content
     * @param  ConfigNotify  $configNotify
     */
    public function workWechat($content, $configNotify)
    {
        $config = json_decode($configNotify->value, true);
        $data = [
            'msgtype' => 'text',
            'text' => [
                'content' => $content,
            ],
        ];

        try {
            $url = $config['webhook'];
            $response = $this->post($url, ['json' => $data]);
            $response = json_decode($response, true);
            if ($response['errcode'] != 0) {
                throw new \Exception($response['errmsg']);
            }
            $this->log->info('Send workWecaht success', [$content, $response]);
        } catch (\Exception $exception) {
            $this->log->error('Send workWecaht fail', [$content, $exception->getMessage()]);
        }
    }

    /**
     * Telegram
     *
     * @param  string  $content
     * @param  ConfigNotify  $configNotify
     */
    public function telegram($content, $configNotify)
    {
        $config = json_decode($configNotify->value, true);

        try {
            $url = sprintf(self::URL_TELEGRAM, $config['token'], $config['chat_id'], urlencode($content));
            $response = $this->post($url);
            $response = json_decode($response, true);
            if ($response['ok'] != true) {
                throw new \Exception($response['description']);
            }
            $this->log->info('Send telegram success', [$content, $response]);
        } catch (\Exception $exception) {
            $this->log->error('Send telegram fail', [$content, $exception->getMessage()]);
        }
    }

    /**
     * Webhook
     *
     * @param  string  $content
     * @param  ConfigNotify  $configNotify
     */
    public function webhook($content, $configNotify)
    {
        $config = json_decode($configNotify->value, true);
        $headers = isset($config['headers']) ? explode("\n", $config['headers']) : [];
        try {
            $url = $config['webhook'];
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = compact('content');
            $options[CURLOPT_HTTPHEADER] = $headers;
            $options[CURLOPT_TIMEOUT] = 10;
            $options[CURLOPT_CONNECTTIMEOUT] = 10;
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = false;

            $ch = curl_init($url);
            curl_setopt_array($ch, $options);
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new \Exception(curl_error($ch));
            }
            $this->log->info('Send webhook success', [$content, $response]);
        } catch (\Exception $exception) {
            $this->log->error('Send webhook fail', [$content, $exception->getMessage()]);
        }
    }

    /**
     * post
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
