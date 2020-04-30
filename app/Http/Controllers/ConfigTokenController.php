<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConfigTokenResource;
use App\Models\ConfigToken;
use Illuminate\Http\Request;

class ConfigTokenController extends Controller
{
    public function view(Request $request)
    {
        $data = [
            'title' => '令牌配置'
        ];
        return view('configToken/index')->with($data);
    }

    /**
     * list data
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $input = $request->input();
        $pageSize = $input['pageSize'] ?? 10;
        $pageNum = $input['pageNum'] ?? 1;
        $data = ConfigToken::orderBy('id', 'desc')
            ->paginate($pageSize, '*', 'page', $pageNum);
        return ConfigTokenResource::collection($data);
    }

    /**
     * data store
     *
     * @param  Request  $request
     * @return array
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:40',
            'api_limit' => 'required|int',
            'description' => 'string|max:255',
        ]);
        $input = $request->all();
        $configToken = ConfigToken::firstOrCreate($input);
        return [
            'success' => $configToken->wasRecentlyCreated,
            'data' => $configToken
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // TODO
    }

    /**
     * delete data
     *
     * @param $id
     * @return array
     */
    public function destroy($id)
    {
        try {
            $res = ConfigToken::find($id)->delete();
        } catch (\Exception $e) {
            $res = false;
        }
        return ['success' => $res];
    }
}
