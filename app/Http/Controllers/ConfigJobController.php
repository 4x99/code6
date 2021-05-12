<?php

namespace App\Http\Controllers;

use App\Models\ConfigJob;
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
        return ConfigJob::orderByDesc('id')->get();
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
            $keywords = explode("\n", $request->input('keyword'));
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
}
