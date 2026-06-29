予約確認のお知らせ

{{ $reservation->firstname }} 様

いつもご利用いただき、ありがとうございます。

明日のご来場をお待ちしています。
ご予約の詳細をご確認ください。

【ご来場予定日時は明日です】

@if ($reservation->article)
イベント・会場名: {{ $reservation->article->title }}
@if ($reservation->article->venue)
（{{ $reservation->article->venue->venue_name }}）
@endif
@else
イベント・会場名: 未設定
@endif

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

ご来場予定日時: {{ $slotDateTime ? $slotDateTime->format('Y年m月d日 H:i') : '未設定' }}

ご予約者様名: {{ $reservation->firstname }} {{ $reservation->lastname ?? '' }}　様


ご不明な点がございましたら、お気軽にお問い合わせください。
明日のご来場をお待ち申し上げております。

---

このメールは自動送信されています。
ご返信いただいてもお答えできません。あらかじめご了承ください。

ーーーーーーーーーーーーーーーーーーーー
株式会社一条工務店
〒135-0042
東京都江東区木場5-10-10
ーーーーーーーーーーーーーーーーーーーー
