<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\QueueJob;
use App\Models\CodeLeak;
use App\Models\ConfigJob;
use App\Models\ConfigToken;
use App\Services\GitHubService;
use App\Utils\SystemUtil;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class HomeController extends Controller
{

    public function view()
    {
        $data = [
            'title' => '应用概况',
            'user' => explode('@', Auth::user()->email, 2)[0],
        ];
        return view('home.index', $data);
    }

    /**
     * 数据指标
     *
     * @return array
     */
    public function metric()
    {
        $data = [
            'codeLeakCount' => CodeLeak::count(),
            'codeLeakPending' => CodeLeak::where('status', CodeLeak::STATUS_PENDING)->count(),
            'codeLeakSolved' => CodeLeak::where('status', CodeLeak::STATUS_SOLVED)->count(),
            'queueJobCount' => QueueJob::count(),
        ];
        return ['success' => true, 'data' => $data];
    }

    /**
     * 系统负载
     *
     * @return array
     */
    public function load()
    {
        return ['success' => true, 'data' => sys_getloadavg()];
    }

    /**
     * 磁盘空间
     *
     * @return array
     */
    public function disk()
    {
        $disk = SystemUtil::disk();
        $data = [
            'used' => SystemUtil::conv($disk['used']),
            'total' => SystemUtil::conv($disk['total']),
            'percent' => $disk['used'] / $disk['total'] * 100,
        ];
        return ['success' => true, 'data' => $data];
    }

    /**
     * 内存使用
     *
     * @return array
     */
    public function memory()
    {
        $memory = SystemUtil::memory();
        if ((int) $memory['MemTotal'] <= 0) {
            return ['success' => false];
        }
        $used = $memory['MemTotal'] - $memory['MemFree'] - $memory['Cached'] - $memory['Buffers'];
        $data = [
            'used' => SystemUtil::conv($used),
            'total' => SystemUtil::conv($memory['MemTotal']),
            'percent' => $used / $memory['MemTotal'] * 100,
        ];
        return ['success' => true, 'data' => $data];
    }

    /**
     * 令牌配额
     *
     * @return array
     */
    public function tokenQuota()
    {
        try {
            $total = ConfigToken::where('status', ConfigToken::STATUS_NORMAL)->sum('api_limit');
            $remaining = ConfigToken::where('status', ConfigToken::STATUS_NORMAL)->sum('api_remaining');
            $used = $total - $remaining;
            $data = [
                [
                    'name' => '可用',
                    'value' => $remaining,
                    'percent' => $total ? $remaining / $total : 0,
                ],
                [
                    'name' => '已用',
                    'value' => $used,
                    'percent' => $total ? $used / $total : 100,
                ],
            ];
            return ['success' => true, 'data' => $data];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 任务统计
     *
     * @return array
     */
    public function jobCount()
    {
        return ['success' => true, 'data' => ConfigJob::count()];
    }

    /**
     * 令牌统计
     *
     * @return array
     */
    public function tokenCount()
    {
        return ['success' => true, 'data' => ConfigToken::count()];
    }

    /**
     * 升级检查
     *
     * @return array
     */
    public function upgradeCheck()
    {
        try {
            $query = ConfigToken::where('status', ConfigToken::STATUS_NORMAL);
            $token = $query->inRandomOrder()->take(1)->value('token');
            $client = (new GitHubService())->createClient($token);
            $release = $client->api('repo')->releases()->latest('4x99', 'code6');
            $new = version_compare($release['tag_name'], VERSION) === 1;
            $data = ['new' => $new, 'version' => $release['tag_name']];
        } catch (\Exception $e) {
            $data = ['new' => false];
        }
        return ['success' => true, 'data' => $data];
    }

    /**
     * 移动端二维码
     *
     * @param  Request  $request
     * @return array
     */
    public function mobileQrCode(Request $request)
    {
        $qrCode = QrCode::format('png')->size(110)->margin(0)->generate($request->input('url'));
        $data = 'data:image/png;base64,'.base64_encode($qrCode);
        return ['success' => true, 'data' => $data];
    }
}
