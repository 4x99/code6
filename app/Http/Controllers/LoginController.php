<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    public function view()
    {
        $data = [
            'title' => '用户登录'
        ];
        return view('login/index')->with($data);
    }

    /**
     * 登录
     *
     * @param  Request  $request
     * @return array|bool[]
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'email', 'exists:user,email'],
                'password' => ['required', 'string', 'max:255'],
            ]);
            if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                throw new \Exception('登录失败，邮箱或密码错误！');
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
