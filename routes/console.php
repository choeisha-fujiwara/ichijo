<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// 予約日前日10時に確認メールを送信
Schedule::command('reservation:send-reminders')
    ->dailyAt('10:00')
    ->name('reservation-reminders')
    ->description('予約日前日10時に予約確認メールを送信');
