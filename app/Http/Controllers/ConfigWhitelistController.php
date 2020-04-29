<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfigWhitelistController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'title' => '白名单配置'
        ];
        return view('whiteList/index')->with($data);
    }
}
