<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConfigJobResource;
use App\Services\ConfigJobService;
use Illuminate\Http\Request;

class ConfigJobController extends Controller
{
    private $service;

    public function __construct(ConfigJobService $configJobService)
    {
        $this->service = $configJobService;
    }

    public function view(Request $request)
    {
        $data = [
            'title' => '任务配置'
        ];
        return view('configJob/index')->with($data);
    }

    /**
     * configJob list
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $params = [
            'pageSize' => $request->input('pageSize', 10),
        ];
        $data = $this->service->index($params);
        return ConfigJobResource::collection($data);
    }

    /**
     * configJob store
     *
     * @param  Request  $request
     * @return array|bool[]
     */
    public function store(Request $request)
    {
        $params = [
            'keyword' => $request->input('keyword'),
            'scan_page' => $request->input('scan_page'),
            'description' => $request->input('description'),
            'scan_interval_min' => $request->input('scan_interval_min'),
        ];
        try {
            $this->validate($params, $this->_rules(), $this->_messages());
            $this->service->store($params);
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
        return ['success' => true];
    }

    /**
     * configJob update
     *
     * @param  Request  $request
     * @param $id
     * @return array|bool[]
     */
    public function update(Request $request, $id)
    {
        $params = [
            'id' => $id,
            'keyword' => $request->input('keyword'),
            'scan_page' => $request->input('scan_page'),
            'description' => $request->input('description'),
            'scan_interval_min' => $request->input('scan_interval_min'),
        ];
        try {
            $this->validate($params, $this->_updateRules($id), $this->_messages());
            $this->service->update($id, $params);
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
        return ['success' => true];
    }

    /**
     * configJob destroy
     *
     * @param $id
     * @return array|bool[]
     */
    public function destroy($id)
    {
        $params = [
            'id' => $id,
        ];
        try {
            $this->validate($params, $this->_destroyRules(), $this->_messages());
            $this->service->destroy($id);
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
        return ['success' => true];
    }

    /**
     * store validate rules
     *
     * @return string[]
     */
    private function _rules()
    {
        return [
            'keyword' => 'required|string|max:255|unique:config_job,keyword',
            'scan_page' => 'required|integer',
            'description' => 'string|max:255',
            'scan_interval_min' => 'required|integer',
        ];
    }

    /**
     * update validate rules
     *
     * @param $id
     * @return string[]
     */
    private function _updateRules($id)
    {
        $rules = $this->_rules();
        $rules['id'] = 'required|integer|exists:config_job,id';
        $rules['keyword'] = 'required|string|max:255|unique:config_job,keyword,'.$id;
        return $rules;
    }

    /**
     * destroy validate rules
     *
     * @return string[]
     */
    private function _destroyRules()
    {
        return [
            'id' => 'required|integer|exists:config_job,id',
        ];
    }

    /**
     * validate messages
     *
     * @return string[]
     */
    private function _messages()
    {
        return [
            'id.exists' => 'configJob does not exist',
            'keyword.required' => 'keyword is required',
            'keyword.unique' => 'keyword already exist',
            'scan_page.required' => 'scan_page is required',
            'scan_interval_min.required' => 'scan_interval_min is required',
        ];
    }

}
