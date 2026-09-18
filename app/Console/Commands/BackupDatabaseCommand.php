<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\DatabaseBackupService;
use App\Services\LogService;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database 
                            {--telegram : Kirim hasil backup ke Telegram} 
                            {--clean : Bersihkan backup lama sesuai batas retensi} 
                            {--name= : Nama file kustom}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat file backup database terkompresi (.sql.gz) dan mengirimkannya ke Telegram (opsional).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Memulai proses backup database...');
        $startTime = microtime(true);

        $customName = $this->option('name');
        $backup = DatabaseBackupService::createBackup($customName);

        if (!$backup['success']) {
            $this->error('❌ ' . ($backup['message'] ?? 'Gagal membuat backup database.'));
            return Command::FAILURE;
        }

        $duration = round(microtime(true) - $startTime, 2);
        $this->info("✅ Backup berhasil dibuat: {$backup['filename']} ({$backup['formatted_size']}) dalam {$duration} detik.");

        LogService::record('system', 'scheduled_backup', "Membuat backup otomatis {$backup['filename']} ({$backup['formatted_size']})");

        // Send to Telegram if requested
        if ($this->option('telegram')) {
            $this->info('📤 Mengirim file backup ke Telegram...');
            $teleRes = DatabaseBackupService::sendToTelegram($backup['path']);

            if ($teleRes['success']) {
                $this->info('✅ File backup berhasil dikirim ke Telegram!');
            } else {
                $this->warn('⚠️ ' . $teleRes['message']);
            }
        }

        // Clean old backups if requested
        if ($this->option('clean')) {
            $retentionDays = (int) Setting::get('telegram_backup_retention', '7');
            if ($retentionDays > 0) {
                $this->info("🧹 Membersihkan file backup yang lebih tua dari {$retentionDays} hari...");
                $deletedCount = DatabaseBackupService::cleanOldBackups($retentionDays);
                $this->info("🗑️ {$deletedCount} file backup lama berhasil dibersihkan.");
            }
        }

        $this->info('🎉 Proses backup database selesai sepenuhnya.');
        return Command::SUCCESS;
    }
}
