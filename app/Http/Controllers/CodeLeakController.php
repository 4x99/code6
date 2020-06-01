<?php

namespace App\Http\Controllers;

use App\Models\CodeLeak;
use App\Models\CodeFragment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CodeLeakController extends Controller
{
    public function view()
    {
        $data = ['title' => '扫描结果'];
        return view('codeLeak.index', $data);
    }

    /**
     * 扫描结果列表
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $query = CodeLeak::query();

        $query->when($request->input('sdate'), function ($query, $value) {
            return $query->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime($value)));
        });

        $query->when($request->input('edate'), function ($query, $value) {
            return $query->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime($value)));
        });

        $query->when($request->filled('status'), function ($query) use ($request) {
            return $query->where('status', $request->input('status'));
        });

        foreach (['repo_owner', 'repo_name', 'keyword', 'path'] as $field) {
            $query->when($request->input($field), function ($query, $value) use ($field) {
                return $query->where($field, 'like', "%$value%");
            });
        }

        $perPage = $request->input('limit', 100);
        return $query->orderByDesc('id')->paginate($perPage);
    }

    /**
     * 更新扫描结果
     *
     * @param  Request  $request
     * @param $id
     * @return array
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate(['status' => 'integer']);
            $data = $request->only('status', 'description');
            $data['handle_user'] = Auth::user()->email;
            $codeLeak = CodeLeak::find($id);
            $success = $codeLeak->update($data);
            return ['success' => $success, 'data' => $codeLeak];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 删除扫描结果
     *
     * @param  Request  $request
     * @param $id
     * @return array
     */
    public function destroy(Request $request, $id)
    {
        try {
            if ($success = (bool) CodeLeak::destroy($id)) {
                $uuid = $request->input('uuid');
                CodeFragment::where('uuid', $uuid)->delete(); // 删除代码片段
            }
            return ['success' => $success, 'message' => $success ? '删除成功！' : '删除失败！'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 批量更新扫描结果
     *
     * @param  Request  $request
     * @return array
     */
    public function batchUpdate(Request $request)
    {
        try {
            $uuid = json_decode($request->input('uuid'), true);
            $data = $request->only('status', 'description');
            $data['handle_user'] = Auth::user()->email;
            $success = CodeLeak::whereIn('uuid', $uuid)->update($data);
            return ['success' => $success];
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }

    /**
     * 批量删除扫描结果
     *
     * @param  Request  $request
     * @return array
     */
    public function batchDestroy(Request $request)
    {
        try {
            $uuid = json_decode($request->input('uuid'), true);
            if ($success = CodeLeak::whereIn('uuid', $uuid)->delete()) {
                $success = CodeFragment::whereIn('uuid', $uuid)->delete();
            }
            return ['success' => $success];
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }
}
