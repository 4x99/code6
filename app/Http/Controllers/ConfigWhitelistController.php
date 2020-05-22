<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfigWhitelist;

class ConfigWhitelistController extends Controller
{
    public function view(Request $request)
    {
        $data = ['title' => '白名单配置'];
        return view('configWhitelist.index', $data);
    }

    /**
     * 白名单列表
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $perPage = $request->input('limit', 100);
        return ConfigWhitelist::orderByDesc('id')->paginate($perPage);
    }

    /**
     * 新增白名单
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function store(Request $request)
    {
        try {
            $request->validate(['value' => ['required', 'string', 'max:255']]);
            $data = ConfigWhitelist::firstOrCreate($request->all(['value']));
            if (!$data->wasRecentlyCreated) {
                throw new \Exception('操作失败，可能已存在此仓库！');
            }
            return ['success' => true, 'data' => $data];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 删除白名单
     *
     * @param  int  $id
     * @return array
     */
    public function destroy($id)
    {
        try {
            $success = (bool) ConfigWhitelist::destroy($id);
            return ['success' => $success, 'message' => $success ? '删除成功！' : '删除失败！'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
