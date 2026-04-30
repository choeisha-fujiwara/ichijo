<!DOCTYPE html>
<html lang="ja">
    <script>
        transition = performance.getEntriesByType('navigation');
        if (transition[0].type == 'back_forward') {
            location.reload();
        }
    </script>
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>body{margin:0;padding:0;background:#fff;}.loading{width:100vw;height:100vh;background:#fff;position:fixed;top:0;left:0;z-index:99999999;display:flex;justify-content:center;align-items:center;}.loading img{width: 240px;height:auto}</style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&family=Poppins:wght@500&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/overcast/jquery-ui.min.css">
    <link rel="icon" href="{{ asset('images/favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon-180x180.png') }}" sizes="180x180">
    <script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>
    {{-- <link rel="stylesheet" href="{{ asset('build/assets/guest-C9RI55J8.css') }}"> --}}
    @vite(['resources/sass/guest.scss', 'resources/js/guest.js'])
    <title></title>
</head>
<body>
    <div id="loading" class="loading"></div>
	<div class="container">
        <div class="content">
            {{ $slot }}
        </div>
    </div>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</body>
</html>