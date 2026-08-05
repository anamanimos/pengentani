<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MigrateLocalToR2Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:migrate-r2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi seluruh berkas gambar dari penyimpanan lokal (storage/app/public) ke Cloudflare R2';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai migrasi berkas dari lokal ke Cloudflare R2...');

        $localPath = storage_path('app/public');
        if (!File::isDirectory($localPath)) {
            $this->error('Direktori storage/app/public tidak ditemukan.');
            return 1;
        }

        $allFiles = File::allFiles($localPath);
        $filesToMigrate = [];

        foreach ($allFiles as $file) {
            if ($file->getFilename() !== '.gitignore') {
                $filesToMigrate[] = $file;
            }
        }

        $totalFiles = count($filesToMigrate);
        if ($totalFiles === 0) {
            $this->info('Tidak ada berkas lokal yang perlu dimigrasikan.');
            return 0;
        }

        $this->applyR2Config();

        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        $successCount = 0;
        $failedCount = 0;
        $totalBytes = 0;

        foreach ($filesToMigrate as $file) {
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            try {
                $stream = fopen($file->getRealPath(), 'r+');
                $success = Storage::disk('r2')->put($relativePath, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }

                if ($success) {
                    $successCount++;
                    $totalBytes += $file->getSize();
                } else {
                    $failedCount++;
                }
            } catch (\Exception $e) {
                $failedCount++;
                $this->newLine();
                $this->error("Gagal mengunggah {$relativePath}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $formattedBytes = $this->formatBytes($totalBytes);
        $this->info("Migrasi selesai!");
        $this->info("Berhasil dimigrasikan: {$successCount} file ({$formattedBytes})");

        if ($failedCount > 0) {
            $this->warn("Gagal dimigrasikan: {$failedCount} file");
        }

        return 0;
    }

    private function applyR2Config()
    {
        $accessKey = Setting::get('r2_access_key_id', config('filesystems.disks.r2.key'));
        $secretKey = Setting::get('r2_secret_access_key', config('filesystems.disks.r2.secret'));
        $bucket = Setting::get('r2_bucket', config('filesystems.disks.r2.bucket'));
        $url = Setting::get('r2_url', config('filesystems.disks.r2.url'));
        $endpoint = Setting::get('r2_endpoint', config('filesystems.disks.r2.endpoint'));
        $accountId = Setting::get('r2_account_id', config('filesystems.disks.r2.account_id'));

        if (empty($endpoint) && !empty($accountId)) {
            $endpoint = "https://{$accountId}.r2.cloudflarestorage.com";
        }

        config([
            'filesystems.disks.r2.key' => $accessKey,
            'filesystems.disks.r2.secret' => $secretKey,
            'filesystems.disks.r2.bucket' => $bucket,
            'filesystems.disks.r2.url' => $url,
            'filesystems.disks.r2.endpoint' => $endpoint,
            'filesystems.disks.r2.region' => 'auto',
            'filesystems.disks.r2.use_path_style_endpoint' => true,
        ]);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
