<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CodeLeakController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'title' => '扫描结果'
        ];
        return view('codeLeak/index')->with($data);
    }
}
