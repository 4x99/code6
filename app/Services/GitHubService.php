<?php

namespace App\Services;

use Exception;
use App\Models\ConfigToken;
use Github\Client;
use Illuminate\Support\Facades\Log;

class GitHubService
{
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
        while (true) {
            $this->currentKey = ++$this->currentKey % count($this->clients);
            $client = &$this->clients[$this->currentKey];

            if (time() >= $client['reset']) {
                $this->updateClient($client); // 更新接口配额
            }

            if ($client['remaining'] <= 0) {
                sleep(1);
                continue; // 当前客户端配额耗尽
            }

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
            $client['client'] = new Client(null, 'v3.text-match');
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
