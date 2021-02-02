<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;

class LoginController extends Controller
{

    public function view()
    {
        $data = ['title' => '码小六'];
        return view('mobile.login', $data);
    }
}
