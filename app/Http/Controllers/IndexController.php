<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'title' => '应用概况'
        ];
        return view('home/index')->with($data);
    }
}
