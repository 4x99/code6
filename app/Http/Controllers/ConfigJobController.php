<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfigJobController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'title' => '任务配置'
        ];
        return view('configJob/index')->with($data);
    }
}
