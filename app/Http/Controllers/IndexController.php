<?php

namespace App\Http\Controllers;

class IndexController extends Controller
{
    public function view()
    {
        $data = ['title' => '码小六'];
        return view('index.index', $data);
    }
}
