@extends('base')
@section('content')
    <link rel="stylesheet" href="{{ URL::asset('css/login.css?v=') . VERSION }}">
    <img class="bg" src="{{ URL::asset("image/login-bg.jpg") }}">
    <img class="pc" src="{{ URL::asset("image/login-pc.png") }}">
    <div class="table">
        <div class="title-cn">码小六</div>
        <div class="title-en">MA XIAO LIU</div>
        <div class="email">
            <div class="icon"><img src="{{ URL::asset("image/icon/user.png") }}"></div>
            <input type="text" name="email" id="email" placeholder="邮箱">
        </div>
        <div class="password">
            <div class="icon"><img src="{{ URL::asset("image/icon/password.png") }}"></div>
            <input name="password" id="password" type="password" placeholder="密码">
        </div>
        <button class="btn" onclick="login()">登录</button>
    </div>
    <script>
        window.onload = function () {
            if (self != top) {
                top.location.href = '/';
            }
        }

        function login() {
            var email = document.getElementById('email').value;
            var password = document.getElementById('password').value;
            if (!email || !password) {
                tool.toast('邮箱或密码不能为空！', 'error');
                return;
            }
            tool.ajax('POST', '/api/login', {email: email, password: password}, function (rsp) {
                if (rsp.success) {
                    tool.toast('登录成功！', 'success');
                    if (self != top) {
                        top.location.href = '/';
                    } else {
                        window.location = '/';
                    }
                } else {
                    tool.toast(rsp.message, 'error');
                }
            })
        }
    </script>
@endsection
