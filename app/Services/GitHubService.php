<?php

namespace App\Services;

use Closure;
use Exception;
use Github\Client;
use App\Models\ConfigToken;
use Illuminate\Support\Arr;
use Github\HttpClient\Builder;
use Illuminate\Support\Facades\Log;
use Http\Adapter\Guzzle6\Client as GuzzleClient;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

class GitHubService
{
    const HTTP_TIMEOUT = 30;
    const HTTP_DELAY = 2000;
    const HTTP_MAX_RETRIES = 5;
    const GET_CLIENT_TIMEOUT = 1800;
    const HTTP_CODE_UNAUTHORIZED = 401;
    const RATE_LIMIT_UNAUTHENTICATED = 10; // 未授权限制请求频率：10 次 / 分钟

    public $clients = [];
    private $userAgent = 'Code6';
    private $currentKey = -1;

    public function __construct()
    {
        $this->userAgent = config('app.name');
    }

    /**
     * 服务初始化
     */
    public function init()
    {
        $tokens = ConfigToken::inRandomOrder()->get()->pluck('token');
        foreach ($tokens as $token) {
            $client = ['token' => $token];
            $client['client'] = $this->createClient($token);
            if ($this->updateClient($client)) {
                $this->clients[] = $client;
            }
        }
    }

    /**
     * 创建客户端
     *
     * @param $token
     * @return Client
     */
    public function createClient($token)
    {
        $handlerStack = HandlerStack::create(new CurlHandler());
        $handlerStack->push(Middleware::retry($this->retryDecider()));
        $builder = new Builder(GuzzleClient::createWithConfig([
            'timeout' => self::HTTP_TIMEOUT,
            'delay' => self::HTTP_DELAY,
            'headers' => ['User-Agent' => $this->userAgent],
            'handler' => $handlerStack,
        ]));
        $client = new Client($builder, 'v3.text-match');
        if ($token) {
            $client->authenticate($token, null, Client::AUTH_HTTP_TOKEN);
        }
        return $client;
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
            if (time() >= $client['api_reset_at']) {
                $this->updateClient($client);
            }

            // 当前客户端配额是否耗尽
            if ($client['api_remaining'] <= 0) {
                sleep(1);
                continue;
            }

            // 返回客户端
            $client['api_remaining']--;
            $this->updateConfigToken($client);
            return $client['client'];
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
        $code = $resource = null;
        try {
            $resource = $client['client']->api('rate_limit')->getResource('search');
        } catch (Exception $e) {
            $code = $e->getCode();
            Log::debug($e->getMessage(), ['token' => $client['token']]);
        }

        $client['api_limit'] = $resource ? $resource->getLimit() : 0;
        $client['api_reset_at'] = $resource ? $resource->getReset() : null;
        $client['api_remaining'] = $resource ? $resource->getRemaining() : 0;
        if ($code == self::HTTP_CODE_UNAUTHORIZED || $client['api_limit'] == self::RATE_LIMIT_UNAUTHENTICATED) {
            $client['status'] = ConfigToken::STATUS_ABNORMAL;
        } else {
            $client['status'] = $resource ? ConfigToken::STATUS_NORMAL : ConfigToken::STATUS_UNKNOWN;
        }

        $this->updateConfigToken($client);
        return $client['status'] == ConfigToken::STATUS_NORMAL;
    }

    /**
     * 更新数据库
     *
     * @param $client
     * @return mixed
     */
    private function updateConfigToken($client)
    {
        $data = Arr::only($client, ['status', 'api_limit', 'api_remaining']);
        $data['api_reset_at'] = $client['api_reset_at'] ? date('Y-m-d H:i:s', $client['api_reset_at']) : null;
        return ConfigToken::where('token', $client['token'])->update($data);
    }

    /**
     * 决定是否重试请求
     *
     * @return Closure （true：重试 false：不重试）
     */
    protected function retryDecider()
    {
        return function ($retries, Request $request, Response $response = null, RequestException $e = null) {
            // 最大次数
            if ($retries >= self::HTTP_MAX_RETRIES) {
                return false;
            }

            // 请求失败
            if (!is_null($e)) {
                Log::debug('Retry request', ['exception' => $e->getMessage()]);
                return true;
            }

            return false;
        };
    }
}
