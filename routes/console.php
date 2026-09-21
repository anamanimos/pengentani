<?php

use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled Automated Database Backup to Telegram
try {
    $scheduleType = Setting::get('telegram_backup_schedule', 'disabled');
    $backupTime = Setting::get('telegram_backup_time', '02:00');
    $botToken = Setting::get('telegram_bot_token', config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN', '')));
    $chatId = Setting::get('telegram_chat_id', config('services.telegram.chat_id', env('TELEGRAM_CHAT_ID', '')));

    if ($scheduleType !== 'disabled' && !empty($botToken) && !empty($chatId)) {
        $timeParts = explode(':', $backupTime ?: '02:00');
        $hour = str_pad($timeParts[0] ?? '02', 2, '0', STR_PAD_LEFT);
        $minute = str_pad($timeParts[1] ?? '00', 2, '0', STR_PAD_LEFT);
        $timeString = "{$hour}:{$minute}";

        $scheduled = Schedule::command('backup:database --telegram --clean')
            ->timezone('Asia/Jakarta')
            ->name('automated-database-telegram-backup');

        if ($scheduleType === 'daily') {
            $scheduled->dailyAt($timeString);
        } elseif ($scheduleType === 'weekly') {
            $scheduled->weeklyOn(0, $timeString); // Sunday
        } elseif ($scheduleType === 'monthly') {
            $scheduled->monthlyOn(1, $timeString); // 1st of month
        }
    }
} catch (\Throwable $e) {
    // Silently handle if database/settings table is not yet accessible
}
