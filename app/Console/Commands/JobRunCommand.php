<?php

namespace App\Console\Commands;

use Exception;
use App\Models\CodeFragment;
use App\Models\CodeLeak;
use App\Models\ConfigJob;
use App\Models\ConfigWhitelist;
use App\Models\ConfigWhitelistFile;
use App\Models\QueueJob;
use App\Services\GitHubService;
use Github\HttpClient\Message\ResponseMediator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

class JobRunCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code6:job-run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Take jobs from the queue and execute them';

    /**
     * 日志实例
     *
     * @var LoggerInterface
     */
    protected $log;

    /**
     * 扫描白名单
     *
     * @var array
     */
    protected $whitelist;

    /**
     * 按文件名忽略
     *
     * @var array
     */
    protected $whitelistFile;

    /**
     * GitHub Service
     *
     * @var GitHubService
     */
    protected $service;

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

        $this->createGitHubService();
        $this->whitelist = ConfigWhitelist::all()->keyBy('value');
        $this->whitelistFile = ConfigWhitelistFile::pluck('value');

        while ($job = $this->takeJob()) {
            $page = 1;
            $keyword = $job->keyword;
            $configJob = ConfigJob::where('keyword', $keyword)->first();
            $configJob->last_scan_at = date('Y-m-d H:i:s');
            do {
                $client = $this->service->getClient();
                $data = $this->searchCode($client, $keyword, $page);
                $count = $this->store($data, $configJob);
                $this->log->info('Stored', ['keyword' => $keyword, 'page' => $page, 'count' => $count]);
                $lastResponse = ResponseMediator::getPagination($client->getLastResponse());
            } while ($lastResponse['next'] && (++$page <= $configJob->scan_page));
            $configJob->save();
        }

        $this->log->info('Close job');
    }

    /**
     * 初始化 GitHub 服务
     */
    private function createGitHubService()
    {
        $this->service = new GitHubService();
        $this->service->init();
        if (count($this->service->clients) === 0) {
            $this->log->error('No GitHub client available');
            exit;
        }
        $this->log->info('Get GitHub client success', ['count' => count($this->service->clients)]);
    }

    /**
     * 获取任务
     *
     * @return bool|object
     */
    private function takeJob()
    {
        if (!$job = QueueJob::orderBy('created_at')->first()) {
            return false;
        }
        $job->delete();
        return $job;
    }

    /**
     * 搜索代码
     *
     * @param $client
     * @param $keyword
     * @param  int  $page
     * @return array|bool|string
     */
    private function searchCode($client, $keyword, $page = 1)
    {
        try {
            return $client->api('search')->setPage($page)->code($keyword, 'indexed');
        } catch (Exception $e) {
            $this->log->warning($e->getMessage());
            return false;
        }
    }

    /**
     * 保存数据
     *
     * @param $data
     * @param $configJob
     * @return int[]
     */
    private function store($data, $configJob)
    {
        $count = ['leak' => 0, 'fragment' => 0];

        if (!isset($data['items'])) {
            return $count;
        }

        foreach ($data['items'] as $item) {
            $item['keyword'] = $configJob->keyword;
            if (!$uuid = $this->storeLeak($item, $configJob->store_type)) {
                continue;
            }
            $count['leak']++;

            foreach ($item['text_matches'] as $match) {
                if ($this->storeFragment($uuid, $match)) {
                    $count['fragment']++;
                }
            }
        }
        return $count;
    }

    /**
     * 保存代码泄露数据
     *
     * @param $item
     * @param $storeType
     * @return bool|string
     */
    private function storeLeak($item, $storeType)
    {
        $repoOwner = $item['repository']['owner']['login'];
        $repoName = $item['repository']['name'];

        // 扫描白名单
        if ($this->whitelist->has("$repoOwner/$repoName")) {
            return false;
        }

        // 按文件名忽略
        foreach ($this->whitelistFile as $pattern) {
            if (fnmatch($pattern, basename($item['path']))) {
                return false;
            }
        }

        // 匹配 BLOB 值
        preg_match('/\/blob\/(\w{40})\//', $item['html_url'], $matches);
        if (!$blob = $matches[1]) {
            return false;
        }

        // 匹配代码片段
        if (preg_match('/^[a-zA-Z0-9.@]+$/u', $item['keyword'])) {
            $isMatch = false;
            foreach ($item['text_matches'] as $match) {
                if (preg_match("/{$item['keyword']}/", $match['fragment'])) {
                    $isMatch = true;
                }
            }
            if (!$isMatch) {
                return false;
            }
        }

        // 数据入库
        $where = [];
        $uuid = md5("$repoOwner/$repoName/$blob/{$item['path']}");
        switch ($storeType) {
            case configJob::STORE_TYPE_ALL:
                $where = ['uuid' => $uuid];
                break;
            case configJob::STORE_TYPE_FILE_STORE_ONCE:
                $where = ['repo_owner' => $repoOwner, 'repo_name' => $repoName, 'path' => $item['path']];
                break;
            case configJob::STORE_TYPE_REPO_STORE_ONCE:
                $where = ['repo_owner' => $repoOwner, 'repo_name' => $repoName];
                break;
        }

        $leak = CodeLeak::firstOrCreate($where, [
                'uuid' => $uuid,
                'keyword' => $item['keyword'],
                'repo_owner' => $repoOwner,
                'repo_name' => $repoName,
                'repo_description' => (string) $item['repository']['description'],
                'html_url_blob' => $blob,
                'path' => $item['path'],
            ]
        );
        return $leak->wasRecentlyCreated ? $uuid : false;
    }

    /**
     * 保存代码片段数据
     *
     * @param $uuid
     * @param $match
     * @return bool
     */
    private function storeFragment($uuid, $match)
    {
        $fragment = CodeFragment::create([
            'uuid' => $uuid,
            'content' => $match['fragment'],
        ]);
        return $fragment->wasRecentlyCreated;
    }
}
