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
<style>body{margin:0;padding:0;background:#fff;}.loading{width:100vw;height:100vh;background:#fff;position:fixed;top:0;left:0;z-index:99999999;display:flex;justify-content:center;align-items:center;padding-bottom:8vh;}.loading img{width:240px;height:auto}.modal,.menu-modal{opacity:0;visibility:hidden;pointer-events:none}.modal.active,.menu-modal.sp-active{opacity:1;visibility:visible;pointer-events:auto}[x-cloak]{display:none!important}</style>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;600&family=Poppins:wght@500&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/overcast/jquery-ui.min.css">
<link rel="icon" href="{{ asset('images/favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon-180x180.png') }}" sizes="180x180">
@vite(['resources/sass/app.scss', 'resources/js/app.js'])
<title>{{ $title }}｜一条工務店</title>
</head>
<body class="{{ $role }}">
    <div class="container">
        <div id="loading" class="loading"><img src="{{ asset('images/logo.svg') }}" alt="一条工務店"></div>
        <header>
            <div id="header" class="header">
                <div class="header-logo">
                    <h1><span>ICHIJO EVENT MANAGEMENT</span></h1>
                </div>
                <div class="user-state">
                    <p>ログイン日時：{{ $login }}</p>
                    <p>ユーザー名：{{ $name }}</p>
                    @php
                        $currentRole = (string) (auth()->user()?->role ?? $role ?? '');
                        $devRoleSwitchEnabled = (int) session('dev_role_switch_user_id', 0) === (int) auth()->id();
                        $switchableRoles = ['developer', 'system', 'admin', 'manager', 'staff'];
                    @endphp
                    @if ($currentRole === 'developer' || $devRoleSwitchEnabled)
                        <div class="dev-role-switcher">
                            <form method="POST" action="{{ route('dev-role.switch') }}" class="dev-role-switcher-form">
                                @csrf
                                <label for="dev-role-switch">検証ロール</label>
                                <select id="dev-role-switch" name="role">
                                    @foreach ($switchableRoles as $switchRole)
                                        <option value="{{ $switchRole }}" @selected($currentRole === $switchRole)>{{ $switchRole }}</option>
                                    @endforeach
                                </select>
                                <button type="submit">切替</button>
                            </form>
                            @if ($devRoleSwitchEnabled)
                                <form method="POST" action="{{ route('dev-role.reset') }}" class="dev-role-switcher-reset">
                                    @csrf
                                    <button type="submit">解除</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </header>
        <div class="contents">
            <div class="menu">
                <ul>
                <li class="{{ $page == 'index' || $page == 'show' || $page == 'create' || $page == 'edit' ? 'active' : null }}"><a href="{{ route('top.index') }}"><span class="material-icons">list</span><span class="menu-text">記事一覧</span></a></li>
                <li class="{{ $page == 'reservation' ? 'active' : null }}"><a href="{{ route('reservations.index') }}"><span class="material-symbols-outlined">event_available</span><span class="menu-text">予約一覧</span></a></li>
                <li class="{{ $page == 'venue' ? 'active' : null }}"><a href="{{ route('venue.index') }}"><span class="material-symbols-outlined">deployed_code</span><span class="menu-text">会場管理</span></a></li>
                <li class="{{ $page == 'images' ? 'active' : null }}"><a href="{{ route('images.index') }}"><span class="material-symbols-outlined">photo_library</span><span class="menu-text">画像管理</span></a></li>
                @if (in_array($role, ['developer', 'system', 'admin', 'manager']))
                <li class="{{ $page == 'users' ? 'active' : null }}"><a href="{{ route('users.index') }}"><span class="material-symbols-outlined">person</span><span class="menu-text">ユーザー管理</span></a></li>
                @endif
                <li class="{{ $page == 'report' ? 'active' : null }}"><a href="{{ route('report.index') }}"><span class="material-symbols-outlined">grouped_bar_chart</span><span class="menu-text">レポート</span></a></li>
                <li class="logout"><p><span class="material-symbols-outlined icon">logout</span><span class="menu-text">ログアウト</span></p></li>
                </ul>
                <div class="menu-btn">
                    <span class="bar-top"></span>
                    <span class="bar-center"></span>
                    <span class="bar-bottom"></span>
                </div>   
            </div>           
            {{ $slot }}
        </div>
        <footer>
            <div class="footer">
                <p><small>ICHIJO Co., Ltd.</small></p>
            </div>
        </footer>
    </div>
    <div class="menu-modal"></div>
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
    <script type="text/javascript" defer>window.onload=function(){$(function(){$(".loading").fadeOut()})};</script>
    @endif
    <script>
        $(function() {
            let publishPickerFollowListener = null;
            let publishPickerFollowInput = null;
            let publishPickerFollowRafId = null;

            const setPublishPeriodDatepickerClass = function() {
                $("#ui-datepicker-div")
                    .addClass("publish-period-datepicker")
                    .removeClass("reservation-slot-datepicker");
            };

            const clearPublishPeriodDatepickerClass = function() {
                $("#ui-datepicker-div").removeClass("publish-period-datepicker");
            };

            const stopPublishPeriodDatepickerFollow = function() {
                if (publishPickerFollowListener) {
                    window.removeEventListener("resize", publishPickerFollowListener);
                    document.removeEventListener("scroll", publishPickerFollowListener, true);
                }

                if (publishPickerFollowRafId !== null) {
                    window.cancelAnimationFrame(publishPickerFollowRafId);
                }

                publishPickerFollowListener = null;
                publishPickerFollowInput = null;
                publishPickerFollowRafId = null;
            };

            const updatePublishPeriodDatepickerPosition = function() {
                if (!publishPickerFollowInput) {
                    return;
                }

                const $picker = $("#ui-datepicker-div");
                if ($picker.length === 0 || !$picker.is(":visible") || !$picker.hasClass("publish-period-datepicker")) {
                    return;
                }

                const inputRect = publishPickerFollowInput.getBoundingClientRect();
                const pickerWidth = $picker.outerWidth() || 0;
                const pickerHeight = $picker.outerHeight() || 0;
                const viewportPadding = 8;
                const maxLeft = Math.max(viewportPadding, window.innerWidth - pickerWidth - viewportPadding);
                const pickerLeft = Math.min(Math.max(inputRect.left, viewportPadding), maxLeft);

                let pickerTop = inputRect.top - pickerHeight - 24;
                if (pickerTop < viewportPadding) {
                    pickerTop = inputRect.bottom + 8;
                }

                $picker.css({
                    position: "fixed",
                    top: `${pickerTop}px`,
                    left: `${pickerLeft}px`,
                });
            };

            const startPublishPeriodDatepickerFollow = function(input) {
                stopPublishPeriodDatepickerFollow();
                publishPickerFollowInput = input;

                publishPickerFollowListener = function() {
                    if (publishPickerFollowRafId !== null) {
                        return;
                    }

                    publishPickerFollowRafId = window.requestAnimationFrame(function() {
                        publishPickerFollowRafId = null;
                        updatePublishPeriodDatepickerPosition();
                    });
                };

                window.addEventListener("resize", publishPickerFollowListener);
                document.addEventListener("scroll", publishPickerFollowListener, true);
                updatePublishPeriodDatepickerPosition();
            };

            if (window.matchMedia("(max-width: 576px)").matches) {
                $("#from").datepicker({
                    defaultDate: 0,
                    changeMonth: true,
                    numberOfMonths: 1,
                    dateFormat: "yy-mm-dd",
                    beforeShow: function() {
                        setPublishPeriodDatepickerClass();
                        window.setTimeout(() => startPublishPeriodDatepickerFollow(this), 0);
                    },
                    onClose: function(selectedDate) {
                        clearPublishPeriodDatepickerClass();
                        stopPublishPeriodDatepickerFollow();
                        $("#to").datepicker("option", "minDate", selectedDate);
                    }
                });
                $("#to").datepicker({
                    defaultDate: 0,
                    changeMonth: true,
                    numberOfMonths: 1,
                    dateFormat: "yy-mm-dd",
                    beforeShow: function() {
                        setPublishPeriodDatepickerClass();
                        window.setTimeout(() => startPublishPeriodDatepickerFollow(this), 0);
                    },
                    onClose: function(selectedDate) {
                        clearPublishPeriodDatepickerClass();
                        stopPublishPeriodDatepickerFollow();
                        $("#from").datepicker("option", "maxDate", selectedDate);
                    }
                });
            } else {
                $("#from").datepicker({
                    defaultDate: 0,
                    changeMonth: true,
                    numberOfMonths: 2,
                    dateFormat: "yy-mm-dd",
                    beforeShow: function() {
                        setPublishPeriodDatepickerClass();
                        window.setTimeout(() => startPublishPeriodDatepickerFollow(this), 0);
                    },
                    onClose: function(selectedDate) {
                        clearPublishPeriodDatepickerClass();
                        stopPublishPeriodDatepickerFollow();
                        $("#to").datepicker("option", "minDate", selectedDate);
                    }
                });
                $("#to").datepicker({
                    defaultDate: 0,
                    changeMonth: true,
                    numberOfMonths: 2,
                    dateFormat: "yy-mm-dd",
                    beforeShow: function() {
                        setPublishPeriodDatepickerClass();
                        window.setTimeout(() => startPublishPeriodDatepickerFollow(this), 0);
                    },
                    onClose: function(selectedDate) {
                        clearPublishPeriodDatepickerClass();
                        stopPublishPeriodDatepickerFollow();
                        $("#from").datepicker("option", "maxDate", selectedDate);
                    }
                });
            };
        });
    </script>
    <script>
        document.addEventListener('click', function (event) {
            const link = event.target.closest('a[href]');
            if (!link) {
                return;
            }

            const rawHref = link.getAttribute('href') || '';
            if (
                rawHref === '' ||
                rawHref.startsWith('#') ||
                rawHref.startsWith('javascript:') ||
                link.hasAttribute('download') ||
                link.target === '_blank'
            ) {
                return;
            }

            let url;
            try {
                url = new URL(link.href, window.location.href);
            } catch (e) {
                return;
            }

            if (url.origin !== window.location.origin) {
                return;
            }

            const menuButton = document.querySelector('.menu-btn');
            const menu = document.querySelector('.menu');
            const menuModal = document.querySelector('.menu-modal');
            const logoutModal = document.querySelector('.logout-modal');

            if (menuButton) {
                menuButton.classList.remove('active');
            }
            if (menu) {
                menu.classList.remove('sp-active');
            }
            if (menuModal) {
                menuModal.classList.remove('sp-active');
            }
            if (logoutModal) {
                logoutModal.classList.remove('active');
            }

            const loading = document.getElementById('loading');
            if (loading) {
                loading.style.display = 'flex';
                loading.style.opacity = '1';
                loading.classList.remove('loading-end');
            }
        }, true);
    </script>
    {{-- <script src="{{ asset('build/assets/app-C1vQWtqv.js') }}"></script> --}}
    @stack('scripts')
</body>
</html>