予約フォームから新しい投稿がありました。

記事タイトル: {{ $payload['articleTitle'] ?: '未設定' }}
会場: {{ $payload['venueName'] ?: '未設定' }}
予約日時: {{ $payload['reservationDateTime'] ?: '未設定' }}

お名前: {{ $payload['fullName'] ?: '未設定' }}
フリガナ: {{ $payload['fullNameKana'] ?: '未設定' }}
電話番号: {{ $payload['phone'] ?: '未設定' }}
メールアドレス: {{ $payload['email'] ?: '未設定' }}
住所: {{ $payload['address'] ?: '未設定' }}
メモ: {{ $payload['memo'] ?: '未入力' }}
