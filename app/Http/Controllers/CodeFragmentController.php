<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CodeFragmentController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'title' => '代码片段'
        ];
        return view('codeFragment/index')->with($data);
    }
}
