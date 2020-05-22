<?php

namespace App\Http\Controllers;

use App\Models\ConfigToken;
use Illuminate\Http\Request;

class ConfigTokenController extends Controller
{
    public function view()
    {
        $data = ['title' => '令牌配置'];
        return view('configToken.index', $data);
    }

    /**
     * 令牌列表
     *
     * @param  Request  $request
     * @return \Illuminate\Support\Collection
     */
    public function index(Request $request)
    {
        return ConfigToken::orderByDesc('id')->get();
    }

    /**
     * 保存令牌
     *
     * @param  Request  $request
     * @return array
     */
    public function store(Request $request)
    {
        try {
            $request->validate(['token' => ['required', 'string', 'max:255']]);
            $data = ConfigToken::firstOrCreate(
                ['token' => $request->input('token')],
                ['description' => $request->input('description') ?? '']
            );
            if (!$data->wasRecentlyCreated) {
                throw new \Exception('操作失败，可能已存在此令牌！');
            }
            return ['success' => true, 'data' => ConfigToken::find($data->id)];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 更新令牌
     *
     * @param  Request  $request
     * @param $id
     * @return array
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate(['token' => ['required', 'string', 'max:255']]);
            $configToken = ConfigToken::find($id);
            $success = $configToken->update($request->all(['token', 'description']));
            return ['success' => $success, 'data' => $configToken];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 删除令牌
     *
     * @param  int  $id
     * @return array
     */
    public function destroy($id)
    {
        try {
            $success = (bool) ConfigToken::destroy($id);
            return ['success' => $success, 'message' => $success ? '删除成功！' : '删除失败！'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
