<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&family=Poppins:wght@500&display=swap">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/overcast/jquery-ui.min.css">
<script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>
<!--本番環境は npm run build した後の build/assets/***.css をリンク-->
<!--link rel="stylesheet" href="{{-- asset('build/assets/app-BjztzLmn.css') --}}"-->
<link rel="icon" href="{{ asset('images/favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon-180x180.png') }}" sizes="180x180">
<!--ローカル環境用-->
@vite(['resources/css/app.css', 'resources/sass/guest.scss', 'resources/js/guest.js'])
<!---->
<title>{{ config('app.name') }}</title>
</head>
<body>
	<div class="container mx-auto text-base text-slate-700 font-medium">
		<div class="header w-screen h-16 pt-0.5 px-3 flex justify-start items-center border-b">
			<img src="{{ asset('images/logo-yoko.png') }}" class="h-10" alt="神座"/>
		</div>
        {{ $slot }}
        <footer>
            <div class="footer w-full h-12 mt-2 flex justify-center items-center bg-black text-center text-white text-xs">
                <p>©KAMUKURA</p>
            </div>
        </footer>    
    </div>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</body>
</html>