<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DatabaseBackupService
{
    /**
     * Get the backup storage directory.
     */
    public static function getBackupDir(): string
    {
        $dir = storage_path('app/backups');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Create a compressed database backup (.sql.gz).
     *
     * @param string|null $customName
     * @return array
     */
    public static function createBackup(?string $customName = null): array
    {
        $startTime = microtime(true);
        $dbName = config('database.connections.mysql.database', env('DB_DATABASE', 'forge'));
        $timestamp = date('Y-m-d_His');
        
        $safeDbName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $dbName);
        $filename = $customName 
            ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $customName) . '.sql.gz'
            : "backup_{$safeDbName}_{$timestamp}.sql.gz";

        $dir = self::getBackupDir();
        $filePath = $dir . DIRECTORY_SEPARATOR . $filename;

        try {
            self::generateDumpGz($filePath, $dbName);

            $fileSize = File::size($filePath);
            $duration = round(microtime(true) - $startTime, 2);

            return [
                'success' => true,
                'filename' => $filename,
                'path' => $filePath,
                'size' => $fileSize,
                'formatted_size' => self::formatBytes($fileSize),
                'duration_seconds' => $duration,
                'created_at' => Carbon::now(),
                'message' => "Backup database {$filename} ({$duration}s) berhasil dibuat.",
            ];
        } catch (\Throwable $e) {
            if (File::exists($filePath)) {
                @unlink($filePath);
            }
            Log::error('Database backup failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal membuat backup database: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate SQL dump and stream-compress it directly into a .sql.gz file.
     */
    protected static function generateDumpGz(string $destinationPath, string $dbName): void
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '600');

        $pdo = DB::connection()->getPdo();
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // Open gzip stream for writing
        $gz = gzopen($destinationPath, 'wb9');
        if (!$gz) {
            throw new \RuntimeException("Tidak dapat membuka file tujuan untuk penulisan gzip: {$destinationPath}");
        }

        $writeGz = function (string $str) use ($gz) {
            gzwrite($gz, $str);
        };

        // Write SQL Header
        $writeGz("-- ============================================================\n");
        $writeGz("-- PengenTani Database Backup System\n");
        $writeGz("-- Database: `{$dbName}`\n");
        $writeGz("-- Created At: " . date('Y-m-d H:i:s') . "\n");
        $writeGz("-- PHP Version: " . PHP_VERSION . "\n");
        $writeGz("-- ============================================================\n\n");
        $writeGz("SET FOREIGN_KEY_CHECKS = 0;\n");
        $writeGz("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
        $writeGz("SET NAMES utf8mb4;\n");
        $writeGz("SET AUTOCOMMIT = 0;\n");
        $writeGz("START TRANSACTION;\n\n");

        // Fetch all tables
        $tablesStmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        $tables = $tablesStmt->fetchAll(\PDO::FETCH_NUM);

        foreach ($tables as $tableRow) {
            $tableName = $tableRow[0];

            // Table Structure
            $writeGz("-- ------------------------------------------------------------\n");
            $writeGz("-- Table structure for `{$tableName}`\n");
            $writeGz("-- ------------------------------------------------------------\n");
            $writeGz("DROP TABLE IF EXISTS `{$tableName}`;\n");

            $createStmt = $pdo->query("SHOW CREATE TABLE `{$tableName}`");
            $createRow = $createStmt->fetch(\PDO::FETCH_ASSOC);
            $createSql = $createRow['Create Table'] ?? '';
            $writeGz($createSql . ";\n\n");

            // Table Data
            $writeGz("-- Dumping data for table `{$tableName}`\n");
            $columnsStmt = $pdo->query("SHOW COLUMNS FROM `{$tableName}`");
            $columns = $columnsStmt->fetchAll(\PDO::FETCH_ASSOC);
            $colNames = array_map(fn($c) => '`' . $c['Field'] . '`', $columns);
            $colList = implode(', ', $colNames);

            // Fetch data in chunks to prevent memory exhaustion
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$tableName}`");
            $totalRows = (int) $countStmt->fetchColumn();

            if ($totalRows > 0) {
                $chunkSize = 500;
                $offset = 0;

                while ($offset < $totalRows) {
                    $rowsStmt = $pdo->query("SELECT * FROM `{$tableName}` LIMIT {$chunkSize} OFFSET {$offset}");
                    $rows = $rowsStmt->fetchAll(\PDO::FETCH_ASSOC);
                    if (empty($rows)) {
                        break;
                    }

                    $insertStatements = [];
                    foreach ($rows as $row) {
                        $values = [];
                        foreach ($columns as $col) {
                            $fieldName = $col['Field'];
                            $val = $row[$fieldName];

                            if (is_null($val)) {
                                $values[] = 'NULL';
                            } elseif (is_numeric($val) && !str_starts_with((string)$val, '0')) {
                                $values[] = $val;
                            } else {
                                $values[] = $pdo->quote((string)$val);
                            }
                        }
                        $insertStatements[] = '(' . implode(', ', $values) . ')';
                    }

                    if (!empty($insertStatements)) {
                        $writeGz("INSERT INTO `{$tableName}` ({$colList}) VALUES\n" . implode(",\n", $insertStatements) . ";\n");
                    }

                    $offset += $chunkSize;
                }
            }

            $writeGz("\n");
        }

        // Write SQL Footer
        $writeGz("SET FOREIGN_KEY_CHECKS = 1;\n");
        $writeGz("COMMIT;\n");
        $writeGz("-- ============================================================\n");
        $writeGz("-- Backup completed at " . date('Y-m-d H:i:s') . "\n");
        $writeGz("-- ============================================================\n");

        gzclose($gz);
    }

    /**
     * List all local backup files.
     *
     * @return array
     */
    public static function listBackups(): array
    {
        $dir = self::getBackupDir();
        $files = File::files($dir);
        $backups = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            // Match .sql.gz, .zip and .sql files
            if (!str_ends_with($filename, '.sql.gz') && !str_ends_with($filename, '.zip') && !str_ends_with($filename, '.sql')) {
                continue;
            }

            $size = $file->getSize();
            $mtime = $file->getMTime();
            $createdAt = Carbon::createFromTimestamp($mtime);

            $backups[] = [
                'filename' => $filename,
                'path' => $file->getPathname(),
                'size' => $size,
                'formatted_size' => self::formatBytes($size),
                'created_at' => $createdAt,
                'created_at_formatted' => $createdAt->translatedFormat('d M Y, H:i:s'),
                'time_ago' => $createdAt->diffForHumans(),
            ];
        }

        // Sort by created_at DESC (newest first)
        usort($backups, function ($a, $b) {
            return $b['created_at']->timestamp <=> $a['created_at']->timestamp;
        });

        return $backups;
    }

    /**
     * Delete a backup file safely.
     *
     * @param string $filename
     * @return bool
     */
    public static function deleteBackup(string $filename): bool
    {
        $cleanName = basename($filename);
        $filePath = self::getBackupDir() . DIRECTORY_SEPARATOR . $cleanName;

        if (File::exists($filePath)) {
            return File::delete($filePath);
        }

        return false;
    }

    /**
     * Clean old backups older than specified days.
     *
     * @param int $keepDays
     * @return int Number of deleted files
     */
    public static function cleanOldBackups(int $keepDays = 7): int
    {
        if ($keepDays <= 0) {
            return 0;
        }

        $dir = self::getBackupDir();
        $files = File::files($dir);
        $cutoff = Carbon::now()->subDays($keepDays)->timestamp;
        $deleted = 0;

        foreach ($files as $file) {
            if ($file->getMTime() < $cutoff) {
                File::delete($file->getPathname());
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Send backup file to Telegram.
     *
     * @param string $filePath
     * @param string|null $customCaption
     * @return array
     */
    public static function sendToTelegram(string $filePath, ?string $customCaption = null): array
    {
        if (!File::exists($filePath)) {
            return [
                'success' => false,
                'message' => 'File backup tidak ditemukan pada storage.',
            ];
        }

        $botToken = Setting::get('telegram_bot_token', config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN', '')));
        $chatId = Setting::get('telegram_chat_id', config('services.telegram.chat_id', env('TELEGRAM_CHAT_ID', '')));

        if (empty($botToken) || empty($chatId)) {
            return [
                'success' => false,
                'message' => 'Telegram Bot Token atau Chat ID belum dikonfigurasi pada Pengaturan Backup.',
            ];
        }

        $filename = basename($filePath);
        $fileSize = self::formatBytes(File::size($filePath));
        $dbName = config('database.connections.mysql.database', env('DB_DATABASE', 'forge'));
        $appName = config('app.name', 'PengenTani');
        $nowStr = Carbon::now()->translatedFormat('d F Y, H:i:s') . ' WIB';

        $caption = $customCaption ?: "📦 <b>Backup Database {$appName}</b>\n\n" .
            "🗄️ <b>Database:</b> <code>{$dbName}</code>\n" .
            "📁 <b>File:</b> <code>{$filename}</code>\n" .
            "💾 <b>Ukuran:</b> <code>{$fileSize}</code>\n" .
            "📅 <b>Waktu:</b> {$nowStr}\n" .
            "🌐 <b>Host:</b> " . (app()->runningInConsole() ? gethostname() : request()->getHost()) . "\n\n" .
            "✅ <i>Status: Berhasil dicadangkan dan dikompresi (.sql.gz)</i>";

        try {
            $url = "https://api.telegram.org/bot{$botToken}/sendDocument";

            $response = Http::timeout(180)
                ->attach('document', file_get_contents($filePath), $filename)
                ->post($url, [
                    'chat_id' => $chatId,
                    'caption' => $caption,
                    'parse_mode' => 'HTML',
                ]);

            $json = $response->json();

            if ($response->successful() && isset($json['ok']) && $json['ok'] === true) {
                return [
                    'success' => true,
                    'message' => "File backup {$filename} berhasil dikirim ke Telegram!",
                    'data' => $json,
                ];
            }

            $errMsg = $json['description'] ?? $response->body();
            return [
                'success' => false,
                'message' => 'Telegram API Error: ' . $errMsg,
                'data' => $json,
            ];
        } catch (\Throwable $e) {
            Log::error('Send to Telegram failed: ' . $e->getMessage(), ['exception' => $e]);
            return [
                'success' => false,
                'message' => 'Gagal mengirim ke Telegram: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Test Telegram Bot Token & Chat ID connection.
     *
     * @param string|null $token
     * @param string|null $chatId
     * @return array
     */
    public static function testTelegramConnection(?string $token = null, ?string $chatId = null): array
    {
        $botToken = $token ?: Setting::get('telegram_bot_token', config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN', '')));
        $targetChatId = $chatId ?: Setting::get('telegram_chat_id', config('services.telegram.chat_id', env('TELEGRAM_CHAT_ID', '')));

        if (empty($botToken)) {
            return [
                'success' => false,
                'message' => 'Telegram Bot Token tidak boleh kosong.',
            ];
        }

        try {
            // Step 1: Verify Bot Token via getMe
            $meResponse = Http::timeout(15)->get("https://api.telegram.org/bot{$botToken}/getMe");
            $meJson = $meResponse->json();

            if (!$meResponse->successful() || empty($meJson['ok'])) {
                return [
                    'success' => false,
                    'message' => 'Bot Token tidak valid: ' . ($meJson['description'] ?? 'Gagal menghubungi Telegram API'),
                ];
            }

            $botUser = $meJson['result'] ?? [];
            $botName = $botUser['first_name'] ?? 'Bot';
            $botUsername = $botUser['username'] ?? '';

            // Step 2: If Chat ID is provided, verify by sending a test message
            if (!empty($targetChatId)) {
                $now = Carbon::now()->translatedFormat('d F Y, H:i:s') . ' WIB';
                $appName = config('app.name', 'PengenTani');
                $testMessage = "🤖 <b>Koneksi Telegram Berhasil!</b>\n\n" .
                    "Sistem backup database <b>{$appName}</b> telah berhasil terhubung dengan bot <b>@{$botUsername}</b>.\n" .
                    "📅 Waktu Uji: {$now}\n" .
                    "✅ Integrasi bot & chat ID aktif.";

                $msgResponse = Http::timeout(15)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $targetChatId,
                    'text' => $testMessage,
                    'parse_mode' => 'HTML',
                ]);

                $msgJson = $msgResponse->json();

                if (!$msgResponse->successful() || empty($msgJson['ok'])) {
                    return [
                        'success' => false,
                        'message' => "Bot (@{$botUsername}) valid, namun gagal mengirim pesan ke Chat ID '{$targetChatId}': " . ($msgJson['description'] ?? 'Pastikan bot sudah di-start di Telegram.'),
                        'bot' => $botUser,
                    ];
                }

                return [
                    'success' => true,
                    'message' => "Koneksi berhasil! Pesan uji coba berhasil dikirim ke Telegram (@{$botUsername}, Chat ID: {$targetChatId}).",
                    'bot' => $botUser,
                ];
            }

            return [
                'success' => true,
                'message' => "Bot Token valid! Terhubung dengan bot: {$botName} (@{$botUsername}). Silakan masukkan Chat ID untuk menguji pengiriman pesan.",
                'bot' => $botUser,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal menguji koneksi Telegram: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format bytes to human readable format.
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
