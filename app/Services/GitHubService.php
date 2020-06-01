<?php

namespace App\Services;

use Exception;
use App\Models\ConfigToken;
use Github\Client;
use Github\HttpClient\Builder;
use Illuminate\Support\Facades\Log;
use Http\Adapter\Guzzle6\Client AS GuzzleClient;

class GitHubService
{
    const GET_CLIENT_TIMEOUT = 1800;
    public $clients = [];
    private $currentKey = -1;

    public function __construct()
    {
        $this->createClients();
    }

    /**
     * 获取客户端
     *
     * @return Client
     */
    public function getClient()
    {
        $start = time();
        while (true) {
            // 超时判断
            if (time() - $start >= self::GET_CLIENT_TIMEOUT) {
                Log::error('Get GitHub client timeout');
                exit;
            }

            // 轮询客户端
            $this->currentKey = ++$this->currentKey % count($this->clients);
            $client = &$this->clients[$this->currentKey];

            // 更新接口配额
            if (time() >= $client['reset']) {
                $this->updateClient($client);
            }

            // 当前客户端配额是否耗尽
            if ($client['remaining'] <= 0) {
                sleep(1);
                continue;
            }

            // 返回客户端
            $client['remaining']--;
            return $client['client'];
        }
    }

    /**
     * 创建客户端
     */
    private function createClients()
    {
        $tokens = ConfigToken::inRandomOrder()->get()->pluck('token');
        foreach ($tokens as $token) {
            $client = ['token' => $token];
            $builder = new Builder(GuzzleClient::createWithConfig(['timeout' => 30]));
            $client['client'] = new Client($builder, 'v3.text-match');
            $client['client']->authenticate($token, null, Client::AUTH_HTTP_TOKEN);
            if ($this->updateClient($client)) {
                $this->clients[] = $client;
            }
        }
    }

    /**
     * 更新客户端
     *
     * @param $client
     * @return bool
     */
    private function updateClient(&$client)
    {
        try {
            if (!$resource = $client['client']->api('rate_limit')->getResource('search')) {
                return false;
            }
            $client['limit'] = $resource->getLimit();
            $client['reset'] = $resource->getReset();
            $client['remaining'] = $resource->getRemaining();
        } catch (Exception $e) {
            Log::warning($e->getMessage(), ['token' => $client['token']]);
            return false;
        }
        return true;
    }
}
