<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>予約確認のお知らせ</title>
</head>
<body style="margin: 0; padding: 24px; background: #f8fafc; color: #334155; font-family: 'Noto Sans JP', sans-serif;">
    <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px;">
        <h1 style="margin: 0 0 12px; font-size: 14px; line-height: 1.4; color: #0f172a;">予約確認のお知らせ</h1>

        <p style="margin: 0 0 12px; font-size: 14px; line-height: 1.8;">{{ $reservation->firstname ?: 'お客様' }} 様</p>
        <p style="margin: 0 0 12px; font-size: 14px; line-height: 1.8;">いつもご利用いただき、ありがとうございます。</p>
        <p style="margin: 0 0 12px; font-size: 14px; line-height: 1.8;">明日のご来場をお待ちしています。ご予約内容をご確認ください。</p>

        <p style="margin: 0 0 12px; font-size: 14px; line-height: 1.8;"><strong>ご来場予定日時は明日です</strong></p>

        <p style="margin: 0 0 8px; font-size: 14px; line-height: 1.8;"><strong>イベント・会場名:</strong></p>
        <p style="margin: 0 0 12px; font-size: 14px; line-height: 1.8;">
            @if ($reservation->article)
                {{ $reservation->article->title }}
                @if ($reservation->article->venue)
                    （{{ $reservation->article->venue->venue_name }}）
                @endif
            @else
                未設定
            @endif
        </p>

        @php
            $slotDateTime = null;
            if (!empty($reservation->reservation_datetime)) {
                try {
                    $slotDateTime = \Carbon\Carbon::parse($reservation->reservation_datetime);
                } catch (\Throwable $e) {
                    $slotDateTime = null;
                }
            }

            if (!$slotDateTime && $reservation->reservationSlot?->date && $reservation->reservationSlot?->start_time) {
                try {
                    $slotDateTime = \Carbon\Carbon::parse($reservation->reservationSlot->date->format('Y-m-d') . ' ' . substr((string) $reservation->reservationSlot->start_time, 0, 5));
                } catch (\Throwable $e) {
                    $slotDateTime = null;
                }
            }
        @endphp

        <p style="margin: 0 0 8px; font-size: 14px; line-height: 1.8;"><strong>ご来場予定日時:</strong></p>
        <p style="margin: 0 0 12px; font-size: 14px; line-height: 1.8;">{{ $slotDateTime ? $slotDateTime->format('Y年m月d日 H:i') : '未設定' }}</p>

        <p style="margin: 0 0 8px; font-size: 14px; line-height: 1.8;"><strong>ご予約者様名:</strong></p>
        <p style="margin: 0 0 24px; font-size: 14px; line-height: 1.8;">{{ $reservation->firstname }} {{ $reservation->lastname ?? '' }} 様</p>

        <p style="margin: 0 0 12px; font-size: 14px; line-height: 1.8;">ご不明な点がございましたら、お気軽にお問い合わせください。</p>
        <p style="margin: 0 0 12px; font-size: 14px; line-height: 1.8;">明日のご来場をお待ち申し上げております。</p>

        <hr style="margin: 20px 0 14px; border: 0; border-top: 1px solid #e2e8f0;">
        <p style="margin: 0 0 12px; font-size: 14px; line-height: 1.8;">
            株式会社一条工務店<br>
            〒135-0042<br>
            東京都江東区木場5-10-10
        </p>

        <p style="margin: 0; font-size: 12px; line-height: 1.8; color: #64748b;">
            このメールは自動送信されています。<br>
            ご返信いただいてもお答えできません。あらかじめご了承ください。
        </p>
    </div>
</body>
</html>
