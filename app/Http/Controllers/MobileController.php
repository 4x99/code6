<?php

namespace App\Http\Controllers;

use App\Models\CodeLeak;
use Illuminate\Support\Facades\Request;

class MobileController extends Controller
{
    public function home()
    {
        $page = Request::query('page', 1);
        $tab = Request::query('tab', 'all');
        $count = CodeLeak::query()->count();
        $data = ['title' => '码小六', 'page' => $page, 'tab' => $tab, 'count' => $count];
        return view('mobile.home', $data);
    }

    public function login()
    {
        $data = ['title' => '码小六'];
        return view('mobile.login', $data);
    }
}
