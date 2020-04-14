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
     * 启动时间（整点）
     *
     * @var int
     */
    protected $time;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = Log::channel($this->signature);
        $this->log->info('Start job');
        $this->time = floor(LARAVEL_START - LARAVEL_START % 60);
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $configJobs = ConfigJob::all();
        $this->log->info('Get config success', ['count' => $configJobs->count()]);
        foreach ($configJobs as $configJob) {
            if (!$configJob->scan_interval_min) {
                continue; // 尚未配置参数
            }

            if (($this->time % ($configJob->scan_interval_min * 60)) !== 0) {
                continue; // 未到启动时间
            }

            $queueJob = QueueJob::firstOrCreate(['keyword' => $configJob->keyword]);
            if (!$queueJob->wasRecentlyCreated) {
                $this->log->Debug('Already exists', ['keyword' => $configJob->keyword]);
                continue; // 存在相同任务
            }

            $this->log->info('Add job', ['keyword' => $configJob->keyword]);
        }
    }

    public function __destruct()
    {
        $this->log->info('Close job');
    }
}
