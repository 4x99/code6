<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\QueueJob;
use App\Models\ConfigJob;
use Psr\Log\LoggerInterface;

class JobAddCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code6:job-add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add scan job to the queue';

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
     * @return void
     */
    public function handle()
    {
        $this->log = Log::channel($this->signature);
        $this->log->info('Start job');

        $configJobs = ConfigJob::all();
        $time = floor(LARAVEL_START - LARAVEL_START % 60); // 启动时间（整点）
        $this->log->info('Get config success', ['count' => $configJobs->count()]);
        foreach ($configJobs as $configJob) {
            if (!$configJob->scan_interval_min) {
                continue; // 尚未配置参数
            }

            if (($time % ($configJob->scan_interval_min * 60)) !== 0) {
                continue; // 未到启动时间
            }

            $job = ['keyword' => $configJob->keyword];
            $queueJob = QueueJob::firstOrCreate($job);
            if (!$queueJob->wasRecentlyCreated) {
                $this->log->Debug('Job already exists', $job);
                continue; // 存在相同任务
            }

            $this->log->info('Add job', $job);
        }

        $this->log->info('Close job');
    }
}
