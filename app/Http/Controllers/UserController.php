<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * 修改密码
     *
     * @param  Request  $request
     * @return array
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'password_current' => ['required', 'string', 'max:255'],
                'password_new' => ['required', 'string', 'max:255', 'confirmed'],
            ]);
            $user = Auth::user();
            if (!Hash::check($request->password_current, $user->getAuthPassword())) {
                throw new \Exception('操作失败，当前密码错误！');
            }
            $success = $user->update(['password' => Hash::make($request->password_new)]);
            return ['success' => $success];
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }
}
