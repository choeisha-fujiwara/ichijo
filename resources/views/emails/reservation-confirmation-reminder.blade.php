<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>予約確認</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #2563eb;
            color: #fff;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background: #f8fafc;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-top: none;
        }
        .section {
            margin: 20px 0;
        }
        .label {
            font-weight: 600;
            color: #475569;
            margin: 10px 0 5px;
        }
        .value {
            color: #1e293b;
            padding: 10px;
            background: #fff;
            border-left: 3px solid #2563eb;
            margin-bottom: 10px;
        }
        .footer {
            background: #f1f5f9;
            padding: 20px;
            border-radius: 0 0 8px 8px;
            font-size: 12px;
            color: #64748b;
            text-align: center;
            border: 1px solid #e2e8f0;
            border-top: none;
        }
        .alert {
            background: #fef3c7;
            border-left: 3px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>予約確認のお知らせ</h1>
        </div>
        
        <div class="content">
            <p>{{ $reservation->firstname }} 様</p>
            
            <p>いつもご利用いただき、ありがとうございます。</p>
            
            <p>明日のご来場をお待ちしています。<br>
            ご予約の詳細をご確認ください。</p>

            <div class="alert">
                <strong>ご来場予定日時は明日です</strong>
            </div>

            <div class="section">
                <div class="label">イベント・会場名</div>
                <div class="value">
                    @if ($reservation->article)
                        {{ $reservation->article->title }}<br>
                        @if ($reservation->article->venue)
                            （{{ $reservation->article->venue->venue_name }}）
                        @endif
                    @else
                        未設定
                    @endif
                </div>
            </div>

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

            <div class="section">
                <div class="label">ご来場予定日時</div>
                <div class="value">
                    {{ $slotDateTime ? $slotDateTime->format('Y年m月d日 H:i') : '未設定' }}
                </div>
            </div>

            <div class="section">
                <div class="label">ご予約者様名</div>
                <div class="value">
                    {{ $reservation->firstname }} {{ $reservation->lastname ?? '' }}
                </div>
            </div>

            <div class="section">
                <p>ご不明な点がございましたら、お気軽にお問い合わせください。</p>
                <p>明日のご来場をお待ち申し上げております。</p>
            </div>

            <div class="section">
                <p>ーーーーーーーーーーーーーーーーーーーー</p>
                <p>株式会社一条工務店</p>
                <p>〒135-0042</p>
                <p>東京都江東区木場5-10-10</p>
                <p>ーーーーーーーーーーーーーーーーーーーー</p>
            </div>

        </div>

        <div class="footer">
            <p>このメールは自動送信されています。<br>
            ご返信いただいてもお答えできません。あらかじめご了承ください。</p>
        </div>
    </div>
</body>
</html>
