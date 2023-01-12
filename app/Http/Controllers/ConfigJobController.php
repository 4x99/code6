<?php

namespace App\Http\Controllers;

use App\Models\ConfigJob;
use App\Models\QueueJob;
use Illuminate\Http\Request;

class ConfigJobController extends Controller
{
    public function view()
    {
        $data = ['title' => '任务配置'];
        return view('configJob.index', $data);
    }

    /**
     * 任务列表
     *
     * @return \Illuminate\Support\Collection
     */
    public function index()
    {
        $data = ConfigJob::orderByDesc('id')->get();
        foreach ($data as &$item) {
            $item['next_scan_at'] = $this->getNextScanAt($item['scan_interval_min']);
        }
        return $data;
    }

    /**
     * 保存任务
     *
     * @param  Request  $request
     * @return array
     */
    public function store(Request $request)
    {
        try {
            $fail = 0;
            $request->validate(['keyword' => ['required', 'string']]);
            $keywords = explode(PHP_EOL, $request->input('keyword'));
            $keywords = array_filter(array_unique($keywords));
            $data = [
                'scan_page' => $request->input('scan_page', 3),
                'scan_interval_min' => $request->input('scan_interval_min', 60),
                'store_type' => $request->input('store_type', ConfigJob::STORE_TYPE_ALL),
                'description' => $request->input('description') ?? '',
            ];
            foreach ($keywords as $keyword) {
                $result = ConfigJob::firstOrCreate(['keyword' => $keyword], $data);
                !$result->wasRecentlyCreated && $fail++;
            }
            return ['success' => true, 'message' => '操作成功！'.($fail ? "（失败：$fail 个）" : '')];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 更新任务
     *
     * @param  Request  $request
     * @param $id
     * @return array
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate(['keyword' => ['required', 'string', 'max:255']]);
            $fields = ['keyword', 'scan_page', 'scan_interval_min', 'store_type', 'description'];
            $configJob = ConfigJob::find($id);
            $success = $configJob->update($request->all($fields));
            $configJob['next_scan_at'] = $this->getNextScanAt($configJob['scan_interval_min']);
            return ['success' => $success, 'data' => $configJob];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 删除任务
     *
     * @param  int  $id
     * @return array
     */
    public function destroy($id)
    {
        try {
            $success = (bool) ConfigJob::destroy($id);
            return ['success' => $success, 'message' => $success ? '删除成功！' : '删除失败！'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 批量删除任务
     *
     * @param  Request  $request
     * @return array
     */
    public function batchDestroy(Request $request)
    {
        try {
            $id = json_decode($request->input('id'), true);
            $success = ConfigJob::whereIn('id', $id)->delete();
            return ['success' => $success];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 任务队列
     *
     * @return array
     */
    public function queue()
    {
        $data = QueueJob::orderBy('id')->get()->toArray();
        foreach ($data as $k => $v) {
            $data[$k]['status'] = $k == 0 ? 1 : 0;
        }
        return $data;
    }

    /**
     * 下次扫描时间
     *
     * @param $interval
     * @return string
     */
    private function getNextScanAt($interval)
    {
        $nextScanAt = floor(LARAVEL_START - LARAVEL_START % ($interval * 60) + ($interval * 60));
        return date('Y-m-d H:i:s', $nextScanAt);
    }
}
