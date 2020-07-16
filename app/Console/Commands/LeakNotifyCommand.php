<?php

namespace App\Console\Commands;

use App\Models\CodeLeak;
use App\Models\ConfigNotify;
use App\Services\NoticeService;
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
        $now = now();
        $fiveMinutesAgo = now()->subMinutes(5);
        $count = CodeLeak::whereBetween('created_at', [$fiveMinutesAgo, $now])->count();
        if (!$count) {
            return;
        }

        // 消息通知
        $content = "扫描时间：{$now->format('Y-m-d H:i:s')} - {$fiveMinutesAgo->format('Y-m-d H:i:s')}\n";
        $content .= "扫描结果：共发现 {$count} 处代码泄露！\n";
        $noticeService = new NoticeService();
        $configs = ConfigNotify::get();
        foreach ($configs as $config) {
            if (!$config->enable) {
                continue;
            }
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
            }
        }
    }
}
