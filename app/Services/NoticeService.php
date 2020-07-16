<?php

namespace App\Services;

use App\Models\ConfigNotify;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NoticeService
{
    const URL_DING_TALK = 'https://oapi.dingtalk.com/robot/send?access_token=%s';
    const URL_WORK_WECHAT = 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=%s';
    const LOG_CHANNEL = 'code6:notify';
    const EMAIL_FROM_NAME = 'code6';
    const EMAIL_TITLE = '代码泄露通知';

    private $log;

    public function __construct()
    {
        $this->log = Log::channel(self::LOG_CHANNEL);
    }

    /**
     * 邮件通知
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
            $this->log->info('Send email success', [$content, $config]);
        } catch (\Exception $exception) {
            $this->log->error('Send email fail', [$exception->getMessage(), $content, $config]);
        }
    }

    /**
     * 钉钉通知
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
            'at' => [
                'atMobiles' => explode("\n", $config['atMobiles']),
                'isAtAll' => $config['isAtAll']
            ],
        ];
        try {
            $client = new Client();
            $url = sprintf(self::URL_DING_TALK, $config['access_token']);
            $response = $client->post($url, ['json' => $data,]);
            $result = $response->getBody()->getContents();
            $result = json_decode($result, true);

            if ($result['errcode'] != 0) {
                throw new \Exception($result['errmsg']);
            }
            $this->log->info('Send dingTalk success', [$content, $config]);
        } catch (\Exception $exception) {
            $this->log->error('Send dingTalk fail', [$exception->getMessage(), $content, $config]);
        }
    }

    /**
     * 企业微信通知
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
                'mentioned_list' => explode("\n", $config['mentioned_list']),
                'mentioned_mobile_list' => explode("\n", $config['mentioned_mobile_list']),
            ],
        ];

        try {
            $client = new Client();
            $url = sprintf(self::URL_WORK_WECHAT, $config['key']);
            $response = $client->post($url, ['json' => $data]);
            $result = $response->getBody()->getContents();
            $result = json_decode($result, true);

            if ($result['errcode'] != 0) {
                throw new \Exception($result['errmsg']);
            }
            $this->log->info('Send workWecaht success', [$content, $config]);
        } catch (\Exception $exception) {
            $this->log->error('Send workWecaht fail', [$exception->getMessage(), $content, $config]);
        }
    }
}
