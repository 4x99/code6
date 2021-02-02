<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ $title }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="version" content="{{ VERSION }}">
    <link rel="icon" href="data:;base64,=">
    <link rel="stylesheet" href="{{ URL::asset('css/vant.css')}}">
    <link rel="stylesheet" href="{{ URL::asset('css/mobile.css')}}">
    <script type="text/javascript" src="{{ URL::asset('js/vue.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/axios.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/vant.min.js') }}"></script>
</head>
<body>
<div id="app">
    <van-form>
        <div class="header">
            <div class="logo"></div>
        </div>
        <van-field v-model="email" label="邮箱" placeholder="邮箱"></van-field>
        <van-field v-model="password" label="密码" type="password" placeholder="密码"></van-field>
        <div style="margin:20px 15px">
            <van-button round block type="info" @click="submit">
                登　　录
            </van-button>
        </div>
    </van-form>
</div>
</body>
<script>
    new Vue({
        el: '#app',
        data: {email: '', password: ''},
        methods: {
            submit: function () {
                var me = this;
                if (!me.email || !me.password) {
                    me.$toast({message: '邮箱或密码不能为空！', position: 'bottom'});
                    return;
                }
                var params = {email: me.email, password: me.password};
                axios.post('/api/login', params).then(function (rsp) {
                    if (rsp.data.success) {
                        window.location = '/mobile/home';
                    } else {
                        me.$toast({message: rsp.data.message, position: 'bottom'});
                    }
                }).catch(function (rsp) {
                    me.$toast.fail(rsp);
                });
            }
        }
    })
</script>
</html>
