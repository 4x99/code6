<?php

namespace App\Services;

use App\Models\ConfigJob;

class ConfigJobService
{
    /**
     * configJob list
     *
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index($params)
    {
        $configJobs = ConfigJob::orderByDesc('id')
            ->paginate($params['pageSize']);
        return $configJobs;
    }

    /**
     * configJob store
     *
     * @param $params
     * @return bool
     */
    public function store($params)
    {
        return (bool) ConfigJob::create($params);
    }

    /**
     * configJob update
     *
     * @param $id
     * @param $params
     * @return bool
     */
    public function update($id, $params)
    {
        return (bool) ConfigJob::find($id)->update($params);
    }

    /**
     * configJob destroy
     *
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function destroy($id)
    {
        return (bool) ConfigJob::destroy($id);
    }
}
