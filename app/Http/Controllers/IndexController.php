<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function view(Request $request)
    {
        $data = ['title' => '码小六'];
        return view('index.index', $data);
    }
}
