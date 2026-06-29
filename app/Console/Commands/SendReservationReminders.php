<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Mail\ReservationConfirmationReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendReservationReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservation:send-reminders';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = '予約日前日10時に予約確認メールを送信';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 明日の日付を取得（10時から24時の予約を対象）
        $tomorrow = now()->addDay()->startOfDay();
        $tomorrowEnd = now()->addDay()->endOfDay();

        // 明日に予約がある、かつメールアドレスがある予約を取得
        $reservations = Reservation::whereBetween('reservation_datetime', [$tomorrow, $tomorrowEnd])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        $count = 0;
        foreach ($reservations as $reservation) {
            try {
                Mail::to($reservation->email)->send(new ReservationConfirmationReminder($reservation));
                $count++;
                $this->info("メール送信完了: {$reservation->firstname} ({$reservation->email})");
            } catch (\Exception $e) {
                $this->error("メール送信失敗: {$reservation->firstname} ({$reservation->email}) - {$e->getMessage()}");
            }
        }

        $this->info("合計 {$count} 件のメールを送信しました");
        return Command::SUCCESS;
    }
}
