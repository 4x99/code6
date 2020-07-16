<?php

namespace App\Console\Commands;

use App\Models\CodeLeak;
use App\Models\ConfigNotify;
use App\Services\NotifyService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class LeakNotifyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code6:leak-notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Leak notify';

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
     */
    public function handle()
    {
        $time = strtotime(date('Y-m-d H:i'));

        $noticeService = new NotifyService();
        $configs = ConfigNotify::get();
        foreach ($configs as $config) {
            // 通知开启 + 通知间隔匹配
            if (!$config->enable || $time % ($config->interval * 60) != 0) {
                continue;
            }

            // 泄露统计
            $end = Carbon::parse()->timestamp($time);
            $start = Carbon::parse()->timestamp($time)->subMinutes($config->interval);
            if (!$count = CodeLeak::query()->whereBetween('created_at', [$start, $end])->count()) {
                continue;
            }

            // 泄露通知
            $content = "开始时间：{$start->format('Y-m-d H:i:s')}\n";
            $content .= "结束时间：{$end->format('Y-m-d H:i:s')}\n";
            $content .= "扫描结果：共发现 {$count} 处代码泄露！";
            switch ($config->type) {
                case ConfigNotify::TYPE_EMAIL:
                    $emailContent = str_replace("\n", '<br/>', $content);
                    $noticeService->email($emailContent, $config);
                    break;
                case ConfigNotify::TYPE_DING_TALK:
                    $noticeService->dingTalk($content, $config);
                    break;
                case ConfigNotify::TYPE_WORK_WECHAT:
                    $noticeService->workWechat($content, $config);
                    break;
                case ConfigNotify::TYPE_TELEGRAM:
                    $noticeService->telegram($content, $config);
                    break;
                case ConfigNotify::TYPE_WEBHOOK:
                    $noticeService->webhook($content, $config);
                    break;
            }
        }
    }
}
