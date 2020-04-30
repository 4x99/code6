<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ $title }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ URL::asset('js/extjs/theme/crisp/theme-crisp.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/style.css?v=') . VERSION }}">
    <script type="text/javascript" src="{{ URL::asset('js/extjs/ext-all.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/extjs/locale-zh_CN.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/script.js?v=' . VERSION) }}"></script>
    <script>
        // 资源版本号
        Ext.Boot.Entry.prototype.getLoadUrl = function () {
            var url = Ext.Boot.canonicalUrl(this.url);
            if (!this.loadUrl) {
                this.loadUrl = (url + (url.indexOf('?') === -1 ? '?' : '&') + 'v={{ VERSION }}');
            }
            return this.loadUrl;
        };
    </script>
</head>
<body>
@yield('content')
</body>
</html>
