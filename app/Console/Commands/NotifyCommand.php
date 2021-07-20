<?php

namespace App\Console\Commands;

use App\Models\CodeLeak;
use App\Models\ConfigCommon;
use App\Models\ConfigNotify;
use App\Services\NotifyService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

class NotifyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code6:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send message notification';

    /**
     * 日志实例
     *
     * @var LoggerInterface
     */
    protected $log;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @uses NotifyService::email()
     * @uses NotifyService::webhook()
     * @uses NotifyService::telegram()
     * @uses NotifyService::feishu()
     * @uses NotifyService::dingTalk()
     * @uses NotifyService::workWechat()
     */
    public function handle()
    {
        $this->log = Log::channel($this->signature);
        $this->log->info('Start notifying user');

        $timestamp = floor(LARAVEL_START - LARAVEL_START % 60);
        $service = new NotifyService();
        $configs = ConfigNotify::where('enable', 1)->get();
        $time = date('H:i:s', $timestamp);

        foreach ($configs as $config) {
            if ($time < $config->start_time || $time > $config->end_time) {
                continue;
            }

            if ($timestamp % ($config->interval_min * 60) != 0) {
                continue;
            }

            $type = $config->type;
            if (!method_exists($service, $type)) {
                continue;
            }

            $data = $this->count($timestamp, $config->interval_min);
            if (!$data['count']) {
                continue;
            }

            $content = $this->getContent($type, $data);
            $config = $config = json_decode($config->value, true);
            $result = $service->$type($content, $config);
            $this->log->info('Send complete', array_merge(['type' => $type], $result));
        }

        $this->log->info('Work done');
    }

    /**
     * @param $timestamp
     * @param $interval
     * @return mixed
     */
    private function count($timestamp, $interval)
    {
        $data = [];
        $data['stime'] = Carbon::createFromTimestamp($timestamp)->subMinutes($interval)->toDateTimeString();
        $data['etime'] = Carbon::createFromTimestamp($timestamp - 1)->toDateTimeString();
        $query = CodeLeak::where('status', CodeLeak::STATUS_PENDING);
        $query = $query->whereBetween('created_at', $data);
        $data['count'] = $query->count();
        return $data;
    }

    /**
     * @param $type
     * @param $data
     * @return string
     */
    private function getContent($type, $data)
    {
        $template = ConfigCommon::getValue(ConfigCommon::KEY_NOTIFY_TEMPLATE);
        $template = json_decode($template, true);
        $templateTitle = $template['title'] ?? NotifyService::TEMPLATE_DEFAULT_TITLE;
        $templateContent = $template['content'] ?? NotifyService::TEMPLATE_DEFAULT_CONTENT;
        $content = $templateTitle.PHP_EOL.$templateContent;
        $content = str_replace(PHP_EOL, $type === ConfigNotify::TYPE_EMAIL ? '<br/><br/>' : "\n\n", $content);

        $content = str_replace('{{stime}}', $data['stime'], $content);
        $content = str_replace('{{etime}}', $data['etime'], $content);
        $content = str_replace('{{count}}', $data['count'], $content);

        return $content;
    }
}
