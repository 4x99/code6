<?php

namespace App\Http\Controllers;

use App\Models\CodeLeak;
use Illuminate\Http\Request;

class CodeLeakController extends Controller
{
    public function view()
    {
        $data = [
            'title' => '扫描结果'
        ];
        return view('codeLeak/index')->with($data);
    }

    /**
     * index data
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $input = $request->input();
        $pageSize = $input['limit'] ?? 100;
        $query = CodeLeak::query();
        $query->when($request->input('keyword'), function ($query, $keyword) {
            return $query->where('keyword', $keyword);
        });
        $query->when($request->input('repo_name'), function ($query, $repoName) {
            return $query->where('repo_name', 'like', "%$repoName%");
        });
        $query->when($request->input('repo_owner'), function ($query, $repoOwner) {
            return $query->where('repo_owner', 'like', "%$repoOwner%");
        });
        $query->when($request->input('status'), function ($query, $status) {
            return $query->where('status', $status);
        });
        $query->when($request->input('sdate'), function ($query, $sdate) {
            return $query->where('created_at', '>', date('Y-m-d 00:00:00', strtotime($sdate)));
        });
        $query->when($request->input('edate'), function ($query, $edate) {
            return $query->where('created_at', '<', date('Y-m-d 23:59:59', strtotime($edate)));
        });
        return $query->orderBy('created_at', 'desc')->paginate($pageSize);
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
        $input = $request->all();
        try {
            $this->validate($input, $this->_rules(), $this->_messages());
            $success = CodeLeak::find($id)->update($input);
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
        return ['success' => $success];
    }

    private function _rules()
    {
        return [
            'status' => 'int',
            'description' => 'string|max:255',
        ];
    }

    private function _messages()
    {
        return [
            'status.type' => 'status type error',
        ];
    }
}
