<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * validate
     *
     * @param  array  $params
     * @param  array  $rules
     * @param  array  $messages
     * @throws \Exception
     */
    public function validate(array $params, array $rules, array $messages = [])
    {
        $validator = Validator::make($params, $rules, $messages);
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
    }
}
