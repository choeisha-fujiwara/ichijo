<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>予約フォーム投稿通知</title>
</head>
<body style="margin: 0; padding: 24px; background: #f8fafc; color: #334155; font-family: 'Noto Sans JP', sans-serif;">
    <div style="max-width: 680px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px;">
        <h1 style="margin: 0 0 16px; font-size: 14px; line-height: 1.4; color: #0f172a;">予約フォームから新しい投稿がありました</h1>
        <table style="width: 100%; border-collapse: collapse; font-size: 14px; line-height: 1.8;">
            <tr>
                <th style="width: 140px; text-align: left; padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #475569;">記事タイトル</th>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0;">{{ $payload['articleTitle'] ?: '未設定' }}</td>
            </tr>
            <tr>
                <th style="text-align: left; padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #475569;">会場</th>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0;">{{ $payload['venueName'] ?: '未設定' }}</td>
            </tr>
            <tr>
                <th style="text-align: left; padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #475569;">予約日時</th>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0;">{{ $payload['reservationDateTime'] ?: '未設定' }}</td>
            </tr>
            <tr>
                <th style="text-align: left; padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #475569;">お名前</th>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0;">{{ $payload['fullName'] ?: '未設定' }}　様</td>
            </tr>
            <tr>
                <th style="text-align: left; padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #475569;">フリガナ</th>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0;">{{ $payload['fullNameKana'] ?: '未設定' }}　様</td>
            </tr>
            <tr>
                <th style="text-align: left; padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #475569;">電話番号</th>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0;">{{ $payload['phone'] ?: '未設定' }}</td>
            </tr>
            <tr>
                <th style="text-align: left; padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #475569;">メールアドレス</th>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0;">{{ $payload['email'] ?: '未設定' }}</td>
            </tr>
            <tr>
                <th style="text-align: left; padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #475569;">住所</th>
                <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0;">{{ $payload['address'] ?: '未設定' }}</td>
            </tr>
            <tr>
                <th style="text-align: left; padding: 8px 10px; color: #475569;">メモ</th>
                <td style="padding: 8px 10px;">{{ $payload['memo'] ?: '未入力' }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
