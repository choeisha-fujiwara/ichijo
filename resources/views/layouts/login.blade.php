
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ @$page }}｜神座 Customer Survey</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&family=Poppins:wght@500&display=swap">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/overcast/jquery-ui.min.css">
        
        <!-- Favicon -->
        <link rel="icon" href="{{ asset('images/favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon-180x180.png') }}" sizes="180x180">
            
        {{-- <link rel="stylesheet" href="{{ asset('build/assets/app-DqXz8-54.css') }}">
        <link rel="stylesheet" href="{{ asset('build/assets/app-AmVkkLD7.css') }}"> --}}
        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/sass/app.scss'])
    </head>
    <body>
        <div id="loading" class="loading"></div>
        <div class="container">
            <div class="login-contents">
                <div class="login-form">
                    <div class="login-logo">
                        <a href="{{ route('login') }}">
                            <x-login-logo/>
                        </a>
                    </div>
                    {{ $slot }}
                </div>
            </div>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.4/i18n/jquery.ui.datepicker-ja.min.js"></script>    
    </body>
</html>