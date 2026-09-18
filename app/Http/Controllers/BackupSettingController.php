<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\DatabaseBackupService;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BackupSettingController extends Controller
{
    /**
     * Display the backup & Telegram settings page.
     */
    public function index()
    {
        $botToken = Setting::get('telegram_bot_token', config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN', '')));
        $chatId = Setting::get('telegram_chat_id', config('services.telegram.chat_id', env('TELEGRAM_CHAT_ID', '')));
        $schedule = Setting::get('telegram_backup_schedule', 'disabled');
        $scheduleTime = Setting::get('telegram_backup_time', '02:00');
        $retentionDays = (int) Setting::get('telegram_backup_retention', '7');

        $backups = DatabaseBackupService::listBackups();
        $totalFiles = count($backups);
        $totalBytes = array_sum(array_column($backups, 'size'));
        $formattedTotalSize = DatabaseBackupService::formatBytes($totalBytes);
        $lastBackup = $backups[0] ?? null;

        $isTelegramConfigured = !empty($botToken) && !empty($chatId);

        return view('settings.backup', compact(
            'botToken',
            'chatId',
            'schedule',
            'scheduleTime',
            'retentionDays',
            'backups',
            'totalFiles',
            'formattedTotalSize',
            'lastBackup',
            'isTelegramConfigured'
        ));
    }

    /**
     * Save Telegram and automated schedule settings.
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'telegram_bot_token' => 'nullable|string|max:255',
            'telegram_chat_id' => 'nullable|string|max:100',
            'telegram_backup_schedule' => 'required|in:disabled,daily,weekly,monthly',
            'telegram_backup_time' => 'required|string|max:10',
            'telegram_backup_retention' => 'required|integer|min:0|max:365',
        ]);

        Setting::set('telegram_bot_token', trim($request->input('telegram_bot_token', '')));
        Setting::set('telegram_chat_id', trim($request->input('telegram_chat_id', '')));
        Setting::set('telegram_backup_schedule', $request->input('telegram_backup_schedule', 'disabled'));
        Setting::set('telegram_backup_time', $request->input('telegram_backup_time', '02:00'));
        Setting::set('telegram_backup_retention', (string) $request->input('telegram_backup_retention', '7'));

        LogService::record('system', 'update_setting', 'Memperbarui pengaturan backup database & Telegram');

        return redirect()->route('settings.backup.index')->with('success', 'Pengaturan backup database dan Telegram berhasil disimpan.');
    }

    /**
     * Create a new database backup manually.
     */
    public function create(Request $request)
    {
        $backup = DatabaseBackupService::createBackup();

        if (!$backup['success']) {
            return redirect()->route('settings.backup.index')->with('error', $backup['message'] ?? 'Gagal membuat backup database.');
        }

        LogService::record('system', 'create_backup', "Membuat file backup database {$backup['filename']} ({$backup['formatted_size']})");

        // If requested to also send to Telegram
        if ($request->boolean('send_telegram')) {
            $teleRes = DatabaseBackupService::sendToTelegram($backup['path']);
            if ($teleRes['success']) {
                return redirect()->route('settings.backup.index')->with('success', "Backup database {$backup['filename']} berhasil dibuat dan dikirim ke Telegram.");
            } else {
                return redirect()->route('settings.backup.index')->with('warning', "Backup database {$backup['filename']} berhasil dibuat, namun gagal dikirim ke Telegram: " . $teleRes['message']);
            }
        }

        // If requested direct download
        if ($request->boolean('direct_download')) {
            return response()->download($backup['path'], $backup['filename'], [
                'Content-Type' => 'application/gzip',
            ]);
        }

        return redirect()->route('settings.backup.index')->with('success', "Backup database {$backup['filename']} ({$backup['formatted_size']}) berhasil dibuat.");
    }

    /**
     * Download an existing backup file.
     */
    public function download(string $filename)
    {
        $cleanName = basename($filename);
        $filePath = DatabaseBackupService::getBackupDir() . DIRECTORY_SEPARATOR . $cleanName;

        if (!File::exists($filePath)) {
            return redirect()->route('settings.backup.index')->with('error', 'File backup tidak ditemukan.');
        }

        LogService::record('system', 'download_backup', "Mengunduh file backup {$cleanName}");

        return response()->download($filePath, $cleanName, [
            'Content-Type' => 'application/gzip',
        ]);
    }

    /**
     * Send an existing backup file to Telegram.
     */
    public function sendTelegram(string $filename)
    {
        $cleanName = basename($filename);
        $filePath = DatabaseBackupService::getBackupDir() . DIRECTORY_SEPARATOR . $cleanName;

        if (!File::exists($filePath)) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'File backup tidak ditemukan.'], 404);
            }
            return redirect()->route('settings.backup.index')->with('error', 'File backup tidak ditemukan.');
        }

        $res = DatabaseBackupService::sendToTelegram($filePath);

        if (request()->wantsJson()) {
            return response()->json($res);
        }

        if ($res['success']) {
            return redirect()->route('settings.backup.index')->with('success', $res['message']);
        }

        return redirect()->route('settings.backup.index')->with('error', $res['message']);
    }

    /**
     * Delete an existing backup file.
     */
    public function delete(string $filename)
    {
        $cleanName = basename($filename);
        $deleted = DatabaseBackupService::deleteBackup($cleanName);

        if ($deleted) {
            LogService::record('system', 'delete_backup', "Menghapus file backup {$cleanName}");

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => "File backup {$cleanName} berhasil dihapus."]);
            }
            return redirect()->route('settings.backup.index')->with('success', "File backup {$cleanName} berhasil dihapus.");
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus file backup.'], 400);
        }

        return redirect()->route('settings.backup.index')->with('error', 'Gagal menghapus file backup.');
    }

    /**
     * Test Telegram Bot Connection via AJAX.
     */
    public function testTelegram(Request $request)
    {
        $token = $request->input('token');
        $chatId = $request->input('chat_id');

        $result = DatabaseBackupService::testTelegramConnection($token, $chatId);

        return response()->json($result);
    }
}
