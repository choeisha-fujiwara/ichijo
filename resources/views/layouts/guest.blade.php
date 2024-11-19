<!DOCTYPE html>
<html lang="ja">
    <script>
        var perfEntries = performance.getEntriesByType("navigation");
        var type = null;
        perfEntries.forEach(function(pe){
            type = pe.type;
        });
        if (type == 'back_forward') {
            location.reload();
        }
    </script>
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>body{margin:0;padding:0;background:#fff;}.loading{width:100%;height:100%;background:#000;position:fixed;top:0;left:0;z-index:99999999;display:flex;justify-content:center;align-items:center;font-family:sans-serif;font-weight:300;}</style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&family=Poppins:wght@500&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/overcast/jquery-ui.min.css">
    <link rel="icon" href="{{ asset('images/favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon-180x180.png') }}" sizes="180x180">
    <script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>
    {{-- <link rel="stylesheet" href="{{ asset('build/assets/guest-C3UbU42Z.css') }}"> --}}
    <!--開発環境用-->
    {{-- @vite(['resources/css/app.css', 'resources/sass/guest.scss', 'resources/js/guest.js']) --}}
    <!---->
    <title>{{ config('app.name') }}</title>
</head>
<body>
    <div id="loading" class="loading"></div>
	<div class="container mx-auto text-base text-slate-700 font-medium">
		<div class="header w-screen h-16 pt-0.5 px-3 flex justify-start items-center border-b">
			<img src="{{ asset('images/logo-landscape.png') }}" class="h-10" alt="神座"/>
		</div>
        {{ $slot }}
        <footer>
            <div class="footer w-full h-12 mt-2 flex justify-center items-center bg-black text-center text-white text-xs">
                <p>©KAMUKURA</p>
            </div>
        </footer>    
    </div>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="{{ asset('build/assets/guest-DdtDbG1h.js') }}"></script>
</body>
</html>