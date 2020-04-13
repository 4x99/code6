<?php

namespace App\Console\Commands;

use App\Models\CodeFragment;
use App\Models\CodeLeak;
use App\Models\ConfigJob;
use App\Models\ConfigWhiteList;
use App\Models\QueueJob;
use App\Services\GithubService;
use Github\Client;
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
     * 白名单
     *
     * @var array
     */
    protected $whiteList;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->whiteList = ConfigWhiteList::pluck('value')->all();
        $this->log = Log::channel($this->signature);
        $this->log->info('Start job');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $githubService = new GithubService();
        if (!count($githubService->clients)) {
            $this->log->error('github clients is empty');
            return;
        }
        while (true) {
            $queueJob = QueueJob::orderBy('created_at', 'asc')->first();
            if (!$queueJob) {
                $this->log->info('queue job is empty');
                return;
            }
            try {
                if (!$queueJob->delete()) {
                    $this->log->error('delete job is fail', $queueJob->attributesToArray());
                    return;
                }
            } catch (\Exception $e) {
                $this->log->error('delete job is execption:'.$e->getMessage());
                return;
            }
            $keyword = $queueJob->keyword;
            $configJob = ConfigJob::where('keyword', $keyword)->first();
            $client = $githubService->getClient();
            $res = $client->api('search')->code($keyword, 'indexed');
            $page = $this->getPagination($client);
            $i = 1; //标记遍历页数
            $this->store($res['items'], $keyword);
            while (($url = $page['next']) && (++$i <= $configJob->scan_page)) {
                $client = $githubService->getClient();
                $res = $this->requestUrl($client, $url);
                $this->store($res['items'], $keyword);
                $page = $this->getPagination($client);
            }
            $configJob->last_scan_at = date('Y-m-d H:i:s');
            $configJob->save();
        }
    }

    /**
     * 获取客户端最后一次请求的分页数据
     *
     * @param  Client  $client
     * @return array|void
     */
    private function getPagination(Client $client)
    {
        return ResponseMediator::getPagination($client->getLastResponse());
    }

    /**
     * 通过客户端及请求地址获取数据
     *
     * @param  Client  $client
     * @param $url
     * @return array|bool|string
     */
    private function requestUrl(Client $client, $url)
    {
        $result = false;
        try {
            $result = $client->getHttpClient()->get($url);
        } catch (\Http\Client\Exception $e) {
            $this->log->warning($e->getMessage());
        }
        return $result ? ResponseMediator::getContent($result) : false;
    }

    /**
     * 保存代码泄漏信息
     *
     * @param $items
     * @param $keyword
     */
    private function store($items, $keyword)
    {
        if (!$items) {
            return;
        }
        $num = 0;
        $pattern = '/blob\/(\w+)/'; //匹配 blob
        foreach ($items as $item) {
            $repoOwner = $item['repository']['owner']['login'];
            $repoName = $item['repository']['name'];
            $repoDesc = $item['repository']['description'] ?: '';
            $path = $item['path'];
            if (in_array("$repoOwner/$repoName", $this->whiteList)) { //白名单过滤
                continue;
            }
            preg_match($pattern, $item['html_url'], $matches);
            if (!$blob = $matches[1]) {
                continue;
            }
            $uuid = md5("$repoOwner/$repoName/$blob/$path");
            $codeLeak = CodeLeak::firstOrCreate(
                ['uuid' => $uuid],
                [
                    'keyword' => $keyword,
                    'repo_owner' => $repoOwner,
                    'repo_name' => $repoName,
                    'repo_description' => $repoDesc,
                    'blob' => $blob,
                    'path' => $path,
                ]
            );
            if (!$codeLeak || !$codeLeak->wasRecentlyCreated) { //该泄漏已存在
                continue;
            }
            $num++;
            foreach ($item['text_matches'] as $match) {
                CodeFragment::create([
                    'uuid' => $uuid,
                    'content' => $match['fragment'],
                ]);
            }
        }
        $this->log->info('add new code leak', ['num' => $num, 'keyword' => $keyword]);
    }

    public function __destruct()
    {
        $this->log->info('Close job');
    }
}
