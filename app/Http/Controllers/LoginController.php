<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    public function view()
    {
        $data = ['title' => '码小六'];
        return view('login.index', $data);
    }

    /**
     * 用户登录
     *
     * @param  Request  $request
     * @return array
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string', 'max:255'],
            ]);
            if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                throw new \Exception('邮箱或密码错误！');
            }
            return ['success' => true, 'data' => Auth::user()];
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }

    /**
     * 退出登录
     *
     * @return array|bool[]
     */
    public function logout()
    {
        try {
            Auth::logout();
            return ['success' => true];
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }
}
