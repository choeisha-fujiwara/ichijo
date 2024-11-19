<!DOCTYPE html>
<html lang="ja">
{{-- <html lang="ja" data-theme="dark"> --}}
<head>
<script>
    transition = performance.getEntriesByType('navigation');
    if (transition[0].type == 'back_forward') {
        location.reload();
    }
</script>
<meta charset="UTF-8">
<link rel="canonical" href="">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="format-detection" content="telephone=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex, nofollow, noarchive" />
<style>body{margin:0;padding:0;background:#fff;}.loading{width:100vw;height:100vh;background:#fff;position:fixed;top:0;left:0;z-index:99999999;display:flex;justify-content:center;align-items:center;}.loading img{width: 240px;height:auto}</style>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;600&family=Poppins:wght@500&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/overcast/jquery-ui.min.css">
<link rel="icon" href="{{ asset('images/favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon-180x180.png') }}" sizes="180x180">
{{-- <link rel="stylesheet" href="{{ asset('build/assets/app-DHDO2cVn.css') }}"> --}}
@vite(['resources/sass/app.scss', 'resources/js/app.js'])
<title>{{ $title }}｜神座 Customer Survey</title>
</head>
<body class="{{ $role }}">
    <div class="container">
        <div id="loading" class="loading"><img src="{{ asset('images/logo-landscape.png') }}" alt="KAMUKURA"></div>
        <header>
            <div id="header" class="header">
                <div class="header-logo">
                    <h1><a href="/dashboard/top"><x-application-logo/><span>KAMUKURA Customer Survey<span></a></h1>
                </div>
                <div class="user-name">
                    <h2>{{ $name }}</h2>
                </div>
                <div class="header-contents">
                    @if ($page == 'index' || $page == 'shop')
                    <div class="search-box">
                        <form action="{{ $page == 'index' ? 'search' : 'shop-search' }}" method="POST" class="search-form">
                            @csrf
                            <div class="search">
                                <input type="hidden" name="state" value="{{ @$state }}" />
                                <input type="text" class="search-box" name="keyword" value="{{ @$keyword }}" placeholder="{{ $page == 'index' ? 'キーワード検索' : '店舗検索' }}">
                                <label class="search-btn" for="search-btn"><span class="material-symbols-outlined">search</span></label>
                                <input type="submit" id="search-btn" value="">
                            </div>
                        </form>
                    </div>
                    @endif
                    @if ($role == 'admin' && $page == 'index')
                        <div class="download-btn active">
                            <span class="material-symbols-outlined thin">download</span><span>CSVダウンロード</span>
                        </div>
                    @endif
                </div>    
            </div>
        </header>
        <div class="contents">
            <div class="menu">
                <ul>
                <li class="{{ $page == 'index' || $page == 'show' ? 'active' : null }}"><a href="{{ route('top.index') }}"><span class="material-icons">list</span><span class="menu-text">投稿一覧</span></a></li>
                @if ($role == 'admin')
                <li class="{{ $page == 'shop' ? 'active' : null }}"><a href="{{ route('shop.index') }}"><span class="material-symbols-outlined">store</span><span class="menu-text">店舗一覧</span></a></li>
                @endif
                <li class="{{ $page == 'report' ? 'active' : null }}"><a href="{{ route('report.index') }}"><span class="material-symbols-outlined">grouped_bar_chart</span><span class="menu-text">レポート</span></a></li>
                <li class="logout"><p><span class="material-symbols-outlined icon">logout</span><span class="menu-text">ログアウト</span></p></li>
                </ul>
                {!! $count != '0' ? '<p class="badge">'.$count.'</p>' : null !!}
            </div>           
            {{ $slot }}
        </div>
        <footer>
            <div class="footer">
                <p><small>RISOUJITSUGYO Co., Ltd.</small></p>
            </div>
        </footer>
    </div>
    <div class="logout-modal modal">
        <div class="logout-box modal-contents">
            <p>ログアウトしますか？</p>
            <div class="logout-buttons">
                <p>キャンセル</p>
                <a href="{{ route('logout') }}">ログアウト</a>
            </div>
        </div>
    </div>
    @if ($page !== 'report')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.4/i18n/jquery.ui.datepicker-ja.min.js"></script>
    @endif
    {{-- <script src="{{ asset('build/assets/app-CZ5vZt5V.js') }}"></script> --}}
    <script>
        $(function() {
            $("#from").datepicker({
                defaultDate: "-1m",
                changeMonth: true,
                numberOfMonths: 2,
                dateFormat: "yy-mm-dd",
                onClose: function(selectedDate) {
                    $("#to").datepicker("option", "minDate", selectedDate);
                }
            });
            $("#to").datepicker({
                defaultDate: "-1m",
                changeMonth: true,
                numberOfMonths: 2,
                dateFormat: "yy-mm-dd",
                onClose: function(selectedDate) {
                    $("#from").datepicker("option", "maxDate", selectedDate);
                }
            });
        });
    </script>
</body>
</html>