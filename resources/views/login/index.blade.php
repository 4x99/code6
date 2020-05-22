@extends('base')
@section('content')
    <style>
        body {
            background: #2870FF !important;
        }

        .container {
            box-sizing: border-box;
            position: absolute;
            top: 50%;
            left: 50%;
            margin-left: -250px;
            margin-top: -220px;
            padding: 50px 30px;
            border-radius: 20px;
            width: 500px;
            height: 400px;
            box-shadow: 5px 5px 10px rgba(0, 0, 0, .15);
            text-align: center;
            background: #FFF;
        }

        .logo {
            margin: 0 auto;
            width: 120px;
            height: 26px;
            background: url('{{ URL::asset('image/logo.png') }}');
        }

        input, button {
            display: block;
            box-sizing: border-box;
            margin: 35px auto;
            width: 350px;
            border-radius: 3px;
        }

        input {
            padding: 10px;
            border: 1px solid #BBB;
            letter-spacing: 2px;
            color: #666;
        }

        input:-webkit-autofill {
            letter-spacing: 2px;
            box-shadow: 0 0 0 1000px #FFF inset;
            -webkit-text-fill-color: #333;
        }

        .btn {
            padding: 12px 10px 12px 20px;
            border: 0;
            text-shadow: 0 -1px 0 rgba(0, 0, 0, .12);
            box-shadow: 0 2px 0 rgba(0, 0, 0, .045);
            cursor: pointer;
            transition: all .3s;
            font-size: 14px;
            letter-spacing: 10px;
            color: #FFF;
            background: #1890FF;
        }

        .btn:hover {
            background: #2F9CFF;
        }

        .register {
            margin-left: 10px;
            font-size: 14px;
            transition: all .3s;
            letter-spacing: 10px;
            color: #AAA;
        }

        .register:hover {
            color: #1890FF;
        }

        code {
            padding: 3px 0;
            background: #F5F5F5;
        }

        .toast-msg {
            box-shadow: none !important;
        }
    </style>
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
                    tool.toast('登录成功！', 'success');
                    new Ext.util.DelayedTask(function () {
                        window.location = '/';
                    }).delay(1000);
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
    </script>
@endsection
