<?php

use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled Automated Database Backup to Telegram
Schedule::command('backup:database --telegram --clean')
    ->hourly()
    ->when(function () {
        try {
            $schedule = Setting::get('telegram_backup_schedule', 'disabled');
            $backupTime = Setting::get('telegram_backup_time', '02:00');
            $botToken = Setting::get('telegram_bot_token');
            $chatId = Setting::get('telegram_chat_id');

            if ($schedule === 'disabled' || empty($botToken) || empty($chatId)) {
                return false;
            }

            $targetHour = !empty($backupTime) ? substr($backupTime, 0, 2) . ':00' : '02:00';
            $currentHourOnly = date('H:00');

            if ($schedule === 'daily') {
                return $currentHourOnly === $targetHour;
            }

            if ($schedule === 'weekly') {
                return date('w') === '0' && $currentHourOnly === $targetHour; // Sunday
            }

            if ($schedule === 'monthly') {
                return date('j') === '1' && $currentHourOnly === $targetHour; // 1st of month
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    })
    ->name('automated-database-telegram-backup');
