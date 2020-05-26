@extends('base')
@section('content')
    <link rel="stylesheet" href="{{ URL::asset('css/login.css?v=') . VERSION }}">

    <div class="container">
        <div class="logo"></div>
        <input type="text" name="email" placeholder="Email">
        <input type="password" name="password" placeholder="Password">
        <button class="btn" onclick="login()">LOGIN</button>
        <a class="register" href="javascript:register()">注册用户</a>
    </div>

    <script>
        function login() {
            var email = Ext.query('input[name=email]')[0].value;
            var password = Ext.query('input[name=password]')[0].value;
            if (!email || !password) {
                tool.toast('邮箱或密码不能为空！', 'error');
                return;
            }

            tool.ajax('POST', '/api/login', {email: email, password: password}, function (rsp) {
                if (rsp.success) {
                    window.location = '/';
                } else {
                    tool.toast(rsp.message, 'error');
                }
            });
        }

        function register() {
            var msg = '<p>请通过命令行方式创建用户：</p>';
            msg += '<p><code>php <项目路径>/artisan code6:user-add <邮箱> <密码></code></p>';

            Ext.Msg.show({
                title: '注册用户',
                iconCls: 'icon-page-star',
                modal: false,
                width: 450,
                message: msg,
            }).removeCls('x-unselectable');
        }

        document.onkeydown = function (event) {
            var e = event || window.event;
            if (e && e.keyCode === 13) {
                login();
            }
        };

        if (window.top !== window.self) {
            window.top.location = window.location;
        }
    </script>
@endsection
