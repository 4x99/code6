<?php

namespace App\Http\Controllers;

use App\Models\ConfigWhitelist;
use Illuminate\Http\Request;

class ConfigWhitelistController extends Controller
{
    public function view(Request $request)
    {
        $data = [
            'title' => '白名单配置'
        ];
        return view('configWhitelist/index')->with($data);
    }

    /**
     * 列表数据
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $input = $request->input();
        $pageSize = $input['limit'] ?? 10;
        $pageNum = $input['page'] ?? 1;
        return ConfigWhitelist::orderBy('id', 'desc')
            ->paginate($pageSize, '*', 'page', $pageNum);
    }

    /**
     * 新增白名单
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function store(Request $request)
    {
        $request->validate([
            'value' => 'required|string|max:255',
        ]);
        $input = $request->all();
        $configWhitelist = ConfigWhitelist::firstOrCreate($input);
        return [
            'success' => $configWhitelist->wasRecentlyCreated,
            'data' => $configWhitelist
        ];
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
            $res = ConfigWhitelist::find($id)->delete();
        } catch (\Exception $e) {
            $res = false;
        }
        return ['success' => $res];
    }
}
