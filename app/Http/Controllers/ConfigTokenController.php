<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConfigTokenResource;
use App\Models\ConfigToken;
use Illuminate\Http\Request;

class ConfigTokenController extends Controller
{
    /**
     * view
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
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
        $page = $input['page'] ?? 1;
        $data = ConfigToken::orderBy('id', 'desc')
            ->paginate($pageSize, '*', 'page', $page);
        return ConfigTokenResource::collection($data);
        return $data;
    }

    /**
     * data store
     *
     * @param  Request  $request
     * @return array
     */
    public function store(Request $request)
    {
        $input = $request->all();
        try {
            $this->validate($input, $this->_rules(), $this->_messages());
            if (ConfigToken::where('token', '=', $input['token'])->exists()) {
                return ['success' => false, 'message' => 'Token existing'];
            }
            $configToken = ConfigToken::firstOrCreate($input);
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
        return [
            'success' => $configToken->wasRecentlyCreated,
            'data' => $configToken,
        ];
    }

    /**
     * update data
     *
     * @param  Request  $request
     * @param $id
     * @return array
     */
    public function update(Request $request, $id)
    {
        $input = [
            'token' => $request->input('token'),
            'api_limit' => $request->input('api_limit'),
            'description' => $request->input('description'),
        ];
        try {
            $this->validate($input, $this->_rules(), $this->_messages());
            $success = ConfigToken::find($id)->update($input);
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
        return ['success' => $success];
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

    /**
     * validate rules
     *
     * @return array
     */
    private function _rules()
    {
        return [
            'token' => 'required|string|max:40',
            'api_limit' => 'required|int',
            'description' => 'string|max:255',
        ];
    }

    /**
     * validate messages
     *
     * @return array
     */
    private function _messages()
    {
        return [
            'token.required' => 'A token is required',
            'api_limit.required' => 'A api_limit is required',
        ];
    }
}
