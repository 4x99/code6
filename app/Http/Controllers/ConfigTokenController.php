<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfigTokenController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'title' => '令牌配置'
        ];
        return view('configToken/index')->with($data);
    }
}
