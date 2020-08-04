<?php

namespace App\Console\Commands;

use App\Models\CodeLeak;
use App\Models\ConfigNotify;
use App\Services\NotifyService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
     * @uses NotifyService::dingTalk()
     * @uses NotifyService::workWechat()
     */
    public function handle()
    {
        $this->log = Log::channel($this->signature);
        $this->log->info('Start notify');

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

            $content = $this->getContent($data);
            $content = implode($type === ConfigNotify::TYPE_EMAIL ? '<br/><br/>' : "\n\n", $content);
            $config = $config = json_decode($config->value, true);
            $result = $service->$type($content, $config);
            $this->log->info('Send complete', array_merge(['type' => $type], $result));
        }

        $this->log->info('Close notify');
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
     * @param $data
     * @return array
     */
    private function getContent($data)
    {
        $content = [];
        $content[] = "码小六消息通知";
        $content[] = "开始时间：{$data['stime']}";
        $content[] = "结束时间：{$data['etime']}";
        $content[] = "本时段共有 {$data['count']} 条未审记录";
        return $content;
    }
}
