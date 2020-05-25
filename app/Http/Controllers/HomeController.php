<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\QueueJob;
use App\Models\CodeLeak;
use App\Models\ConfigJob;
use App\Models\ConfigToken;
use App\Utils\SystemUtil;

class HomeController extends Controller
{
    public function view(Request $request)
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
            'codeLeakPending' => CodeLeak::where('status', 0)->count(),
            'codeLeakSolved' => CodeLeak::where('status', 3)->count(),
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
            $total = ConfigToken::where('status', 1)->sum('api_limit');
            $remaining = ConfigToken::where('status', 1)->sum('api_remaining');
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
}
