<?php

namespace App\Services;

use App\Models\ConfigToken;
use Github\Client;
use Github\HttpClient\Message\ResponseMediator;
use Illuminate\Support\Facades\Log;

class GithubService
{
    const TIMEOUT = 3600;
    public $clients = [];
    private $currentKey = -1;

    public function __construct()
    {
        $this->initClients();
    }

    /**
     * 获取客户端
     * @return Client
     */
    public function getClient()
    {
        $stime = time();
        while (true) {
            $this->currentKey = ++$this->currentKey % count($this->clients);
            $client = &$this->clients[$this->currentKey];
            if (time() >= $client['apiResetAt']) { //token额度重置
                $resource = $client['instance']->api('rate_limit')->getResource('search');
                if ($resource) {
                    $client['apiRemaining'] = $resource->getRemaining();
                    $client['apiResetAt'] = $resource->getReset();
                }
            }
            if ($client['apiRemaining'] <= 0) {
                sleep(1);
                if (time() - $stime > self::TIMEOUT) { //超时退出
                    Log::error('Github get client timeout');
                    exit;
                }
            } else {
                $client['apiRemaining']--;
                return $client['instance'];
            }
        }
    }

    /**
     * 获取客户端最后一次请求的分页数据
     *
     * @param  Client  $client
     * @return array|void
     */
    public function getPagination(Client $client)
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
    public function get(Client $client, $url)
    {
        $result = false;
        try {
            $result = $client->getHttpClient()->get($url);
        } catch (\Http\Client\Exception $e) {
            Log::warning($e->getMessage());
        }
        return $result ? ResponseMediator::getContent($result) : false;
    }

    /**
     * 初始化客户端列表
     */
    private function initClients()
    {
        $tokens = ConfigToken::inRandomOrder()->get()->pluck('token');
        foreach ($tokens as $token) {
            try {
                $client = new Client(null, 'v3.text-match');
                $client->authenticate($token, null, Client::AUTH_HTTP_TOKEN);
                $resource = $client->api('rate_limit')->getResource('search');
                if (!$resource) {
                    continue;
                }
                $this->clients[] = [
                    'token' => $token,
                    'apiLimit' => $resource->getLimit(),
                    'apiRemaining' => $resource->getRemaining(),
                    'apiResetAt' => $resource->getReset(),
                    'instance' => $client,
                ];
            } catch (\Exception $e) {
                Log::warning("Github token:$token is invalid, error msg:".$e->getMessage());
            }
        }
    }
}
