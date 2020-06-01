<?php

namespace App\Console\Commands;

use App\Models\ConfigToken;
use App\Services\GitHubService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

class TokenCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code6:token-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check GitHub API access token';

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

        $count = ['abnormal' => 0, 'normal' => 0];
        $tokens = ConfigToken::all(['token'])->pluck('token');
        $clients = (new GitHubService())->clients;
        $clients = array_column($clients, null, 'token');
        foreach ($tokens as $token) {
            $client = $clients[$token] ?? null;
            $client ? $count['normal']++ : $count['abnormal']++;
            ConfigToken::where('token', $token)->update([
                'status' => (int) $client,
                'api_limit' => (int) $client['limit'],
                'api_remaining' => (int) $client['remaining'],
                'api_reset_at' => $client['reset'] ? date('Y-m-d H:i:s', $client['reset']) : null,
            ]);
        }

        $this->log->info('Close job', $count);
    }
}
