<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <link rel="canonical" href="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="robots" content="noindex, nofollow, noarchive" />
    <style>body{margin:0;padding:0;background:#fff;}.loading{width:100%;height:100%;background:#fff;position:fixed;top:0;left:0;z-index:99999999;display:flex;justify-content:center;align-items:center;font-family:sans-serif;font-weight:300;}</style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&family=Poppins:wght@500&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/overcast/jquery-ui.min.css">
    <title>神座アンケートフォーム</title>
</head>
    <body style="width: 100%; margin: 0; padding: 20% 0 0; font-size: 14px; color: #334155; background-color: #f3f4f6; border: none" topmargin="0" bottomargin="0" leftmargin="0" rightmargin="0" marginwidth="0" marginheight="0">
        <table style="width: 100%; height: auto; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; border-color: inherit; border: none; border-collapse: collapse; background-color: #f3f4f6;">
            <thead>
                <tr>
                    <td style="width: 100%; height: 64px; margin: 0 auto" colspan="5"></td>
                </tr>
            </thead>
            <tbody style="width: 100%; margin: 0 auto; padding: 0; display: block; border-spacing: 0; border-collapse: collapse;" cellspacing="0" cellpadding="0" border="0" align="center">
                <tr>
                    <td style="width: 25%; height: 48px;"></td>
                    <td style="width: 25%; height: 48px; background: #fff; border-top-left-radius: 8px"></td>
                    <td style="width: 320px; height: 48px; background: #fff;"></td>
                    <td style="width: 25%; height: 48px; background: #fff; border-top-right-radius: 8px"></td>
                    <td style="width: 25%; height: 48px;"></td>
                    </td>
                </tr>
                <tr>
                    <td style="width: 25%; height: 63px;"></td>
                    <td style="width: 25%; height: 63px; background: #fff"></td>
                    <td style="width: 320px; height: 63px; background: #fff;">
                        <img src="{{ $message->embed('images/logo-landscape.png') }}" width="320" height="63" style="width: 320px; height: 63px; margin: 0; display: block; text-align: center" alt="KAMUKURA">
                    </td>
                    <td style="width: 25%; height: 63px; background: #fff"></td>
                    <td style="width: 25%; height: 63px;"></td>
                    </td>
                </tr>
                <tr>
                    <td style="width: 25%; height: 72px;"></td>
                    <td style="width: 25%; height: 72px; background: #fff"></td>
                    <td valign="middle" style="width: 320px; height: 72px; margin: 0 auto; background: #fff; text-align: center"><span style="font-family: 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Hira=gino Sans', 'Hiragino Kaku Gthic ProN', Meiryo, sans-serif; font-weight: 500; text-align: center; display: block">神座アンケートフォームからのお知らせ</span></td>
                    <td style="width: 25%; height: 72px; background: #fff"></td>
                    <td style="width: 25%; height: 72px;"></td>
                </tr>
                <tr>
                    <td style="width: 25%; height: 64px;"></td>
                    <td style="width: 25%; height: 64px; background: #fff"></td>
                    <td style="width: 480px; height: 64px; background: #fff;"><span style="font-family: 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Hira=gino Sans', 'Hiragino Kaku Gthic ProN', Meiryo, sans-serif; font-weight: 500; text-align: center; display: block">{{ $name }} 宛に神座アンケートフォームへの投稿がありましたのでご確認ください</span></td>
                    <td style="width: 25%; height: 64px; background: #fff"></td>
                    <td style="width: 25%; height: 64px;"></td>
                </tr>
                <tr>
                    <td style="width: 25%; height: 32px;"></td>
                    <td style="width: 25%; height: 32px; background: #fff"></td>
                    <td style="width: 480px; height: 32px; background: #fff"></td>
                    <td style="width: 25%; height: 32px; background: #fff"></td>
                    <td style="width: 25%; height: 32px;"></td>
                </tr>
                <tr>
                    <td style="width: 25%; height: 48px;"></td>
                    <td style="width: 25%; height: 48px; background: #fff"></td>
                    <td valign="middle" style="width: 100px; height: 24px; margin: 0 auto; background: #2965d9; text-align: center">
                        <a href="https://www.yahoo.co.jp/" style="width: 100px; height: 24px; margin: 0 auto; padding: 0 64px; background: #2965d9; border-top: 12px solid #2965d9; border-bottom: 12px solid #2965d9; border-left: 24px solid #2965d9; border-right: 24px solid #2965d9; display: block; text-align: center; color: #fff; font-family: 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Hira=gino Sans', 'Hiragino Kaku Gthic ProN', Meiryo, sans-serif; font-weight: 500; text-decoration: none; pointer-events: all">確認する</a>
                    </td>
                    <td style="width: 25%; height: 48px; background: #fff"></td>
                    <td style="width: 25%; height: 48px;"></td>
                </tr>
                <tr>
                    <td style="width: 25%; height: 48px;"></td>
                    <td style="width: 25%; height: 48px; background: #fff; border-bottom-left-radius: 8px"></td>
                    <td style="width: 320px; height: 48px; display: block; background: #fff;"></td>
                    <td style="width: 25%; height: 48px; background: #fff; border-bottom-right-radius: 8px"></td>
                    <td style="width: 25%; height: 48px;"></td>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100%; height: 96px; margin: 0 auto box-shadow: 0 4px 6px -1px rgba(0, 0, 0, .2);" colspan="5"></td>
                </tr>
            </tbody>
        </table>
    </body>
</html>