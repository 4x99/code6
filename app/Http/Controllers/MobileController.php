<?php

namespace App\Http\Controllers;

class MobileController extends Controller
{

    public function home()
    {
        $data = ['title' => '码小六'];
        return view('mobile.home', $data);
    }

    public function login()
    {
        $data = ['title' => '码小六'];
        return view('mobile.login', $data);
    }
}
