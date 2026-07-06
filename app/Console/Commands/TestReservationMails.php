<?php

namespace App\Console\Commands;

use App\Mail\ReservationAutoReply;
use App\Mail\ReservationPostedNotification;
use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class TestReservationMails extends Command
{
    protected $signature = 'reservation:test-mails
        {to : 送信先メールアドレス}
        {--article= : 診断に使う記事ID}
        {--name=テスト 太郎 : 自動返信メールに差し込む名前}';

    protected $description = '現在の環境設定で予約通知メールと自動返信メールの疎通確認を行う';

    public function handle(): int
    {
        $to = (string) $this->argument('to');
        $articleId = $this->option('article');
        $name = (string) $this->option('name');

        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error('送信先メールアドレスが不正です。');
            return self::FAILURE;
        }

        $article = null;
        if (! empty($articleId)) {
            $article = Article::with('venue')->find($articleId);
            if (! $article) {
                $this->error('指定された記事が見つかりません。');
                return self::FAILURE;
            }
        }

        $this->line('Mail diagnostics');
        $this->table(['key', 'value'], [
            ['mail.default', (string) Config::get('mail.default')],
            ['mail.from.address', (string) Config::get('mail.from.address')],
            ['mail.from.name', (string) Config::get('mail.from.name')],
            ['mail.mailers.smtp.host', (string) Config::get('mail.mailers.smtp.host')],
            ['mail.mailers.smtp.port', (string) Config::get('mail.mailers.smtp.port')],
            ['mail.mailers.smtp.encryption', (string) Config::get('mail.mailers.smtp.encryption')],
        ]);

        $notificationPayload = [
            'articleTitle' => (string) ($article?->title ?? '予約メール疎通確認'),
            'venueName' => (string) ($article?->venue?->venue_name ?? 'テスト会場'),
            'reservationDateTime' => now()->addDay()->format('Y年m月d日 H:i'),
            'fullName' => $name,
            'fullNameKana' => 'テスト タロウ',
            'phone' => '000-0000-0000',
            'email' => $to,
            'address' => 'テスト住所',
            'memo' => '本メールは疎通確認です。',
        ];

        try {
            Mail::to($to)->send(new ReservationPostedNotification($notificationPayload));
            $this->info('予約通知メールの送信に成功しました。');
        } catch (\Throwable $e) {
            $this->error('予約通知メールの送信に失敗しました: '.$e->getMessage());
            $this->line('Exception: '.$e::class);
            return self::FAILURE;
        }

        try {
            Mail::to($to)->send(new ReservationAutoReply(
                $name,
                (string) ($article?->venue?->venue_name ?? 'テスト会場'),
                now()->addDay()->format('Y年m月d日 H:i')
            ));
            $this->info('自動返信メールの送信に成功しました。');
        } catch (\Throwable $e) {
            $this->error('自動返信メールの送信に失敗しました: '.$e->getMessage());
            $this->line('Exception: '.$e::class);
            return self::FAILURE;
        }

        $this->info('2通とも送信処理に成功しました。');

        return self::SUCCESS;
    }
}