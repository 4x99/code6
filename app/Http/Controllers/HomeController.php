<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function view(Request $request)
    {
        $data = ['title' => '应用概况'];
        return view('home/index')->with($data);
    }
}
