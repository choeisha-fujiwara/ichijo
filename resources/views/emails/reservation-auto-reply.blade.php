<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ご予約ありがとうございます</title>
</head>
<body style="margin: 0; padding: 24px; background: #f8fafc; color: #334155; font-family: 'Noto Sans JP', sans-serif;">
    <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px;">
        <h1 style="margin: 0 0 12px; font-size: 14px; line-height: 1.4; color: #0f172a;">ご予約ありがとうございます</h1>
        <p style="margin: 0 0 12px; font-size: 14px; line-height: 1.8;">{{ $name ?: 'お客様' }} 様</p>
        <p style="margin: 0 0 12px; font-size: 14px; line-height: 1.8;">このたびはご予約いただきありがとうございます。</p>
        <p style="margin: 0 0 12px; font-size: 14px; line-height: 1.8;">内容確認のため、本メールを自動送信しています。</p>
        <p style="margin: 0 0 12px; font-size: 14px; line-height: 1.8;">ご予約内容は以下の通りです。</p>
        @if (!empty($venueName))
            <p style="margin: 0 0 8px; font-size: 14px; line-height: 1.8;"><strong>会場:</strong> {{ $venueName }}</p>
        @endif
        @if (!empty($reservationDateTime))
            <p style="margin: 0 0 24px; font-size: 14px; line-height: 1.8;"><strong>予約日時:</strong> {{ $reservationDateTime }}</p>
        @endif
        <p style="margin: 0 0 12px; font-size: 14px; line-height: 1.8;">ご不明な点がございましたら、お気軽にお問い合わせください。</p>
        <p style="margin: 0 0 12px; font-size: 14px; line-height: 1.8;">今後ともよろしくお願いいたします。</p>
        <hr style="margin: 20px 0 14px; border: 0; border-top: 1px solid #e2e8f0;">
        <p style="margin: 0; font-size: 14px; line-height: 1.8;">
            株式会社一条工務店<br>
            〒135-0042<br>
            東京都江東区木場5-10-10<br>
        </p>
    </div>
</body>
</html>
