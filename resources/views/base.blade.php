<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ $title }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="version" content="{{ VERSION }}">
    <link rel="stylesheet" href="{{ URL::asset('js/extjs/theme/crisp/theme-crisp.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/style.css?v=') . VERSION }}">
    <script type="text/javascript" src="{{ URL::asset('js/extjs/ext-all.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/extjs/theme/crisp/theme-crisp.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/extjs/locale-zh_CN.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/script.js?v=' . VERSION) }}"></script>
</head>
<body>
@yield('content')
</body>
</html>
