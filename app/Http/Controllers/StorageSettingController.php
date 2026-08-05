<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class StorageSettingController extends Controller
{
    public function index()
    {
        $driver = Setting::get('storage_driver', 'r2');
        if ($driver !== 'r2') {
            Setting::set('storage_driver', 'r2');
            $driver = 'r2';
        }
        $accountId = Setting::get('r2_account_id', config('filesystems.disks.r2.account_id', ''));
        $accessKey = Setting::get('r2_access_key_id', config('filesystems.disks.r2.key', ''));
        $secretKey = Setting::get('r2_secret_access_key', config('filesystems.disks.r2.secret', ''));
        $bucket = Setting::get('r2_bucket', config('filesystems.disks.r2.bucket', ''));
        $url = Setting::get('r2_url', config('filesystems.disks.r2.url', ''));
        $endpoint = Setting::get('r2_endpoint', config('filesystems.disks.r2.endpoint', ''));

        // Calculate local files count & size
        $localFilesCount = 0;
        $localTotalBytes = 0;
        $localPath = storage_path('app/public');

        if (File::isDirectory($localPath)) {
            $allFiles = File::allFiles($localPath);
            foreach ($allFiles as $file) {
                if ($file->getFilename() !== '.gitignore') {
                    $localFilesCount++;
                    $localTotalBytes += $file->getSize();
                }
            }
        }

        $localTotalFormatted = $this->formatBytes($localTotalBytes);

        return view('settings.storage', compact(
            'driver',
            'accountId',
            'accessKey',
            'secretKey',
            'bucket',
            'url',
            'endpoint',
            'localFilesCount',
            'localTotalFormatted'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'storage_driver' => 'nullable|string|in:local,r2',
            'r2_account_id' => 'nullable|string|max:255',
            'r2_access_key_id' => 'nullable|string|max:255',
            'r2_secret_access_key' => 'nullable|string|max:255',
            'r2_bucket' => 'nullable|string|max:255',
            'r2_url' => 'nullable|string|max:255',
            'r2_endpoint' => 'nullable|string|max:255',
        ]);

        Setting::set('storage_driver', 'r2');
        Setting::set('r2_account_id', trim($request->r2_account_id ?? ''));
        Setting::set('r2_access_key_id', trim($request->r2_access_key_id ?? ''));
        Setting::set('r2_secret_access_key', trim($request->r2_secret_access_key ?? ''));
        Setting::set('r2_bucket', trim($request->r2_bucket ?? ''));
        
        $r2Url = trim($request->r2_url ?? '');
        if (!empty($r2Url)) {
            $r2Url = rtrim($r2Url, '/');
        }
        Setting::set('r2_url', $r2Url);

        $endpoint = trim($request->r2_endpoint ?? '');
        if (empty($endpoint) && !empty($request->r2_account_id)) {
            $endpoint = "https://" . trim($request->r2_account_id) . ".r2.cloudflarestorage.com";
        }
        Setting::set('r2_endpoint', $endpoint);

        return redirect()->back()->with('success', 'Pengaturan penyimpanan berhasil diperbarui.');
    }

    public function testConnection(Request $request)
    {
        try {
            $accessKey = trim($request->r2_access_key_id ?? Setting::get('r2_access_key_id'));
            $secretKey = trim($request->r2_secret_access_key ?? Setting::get('r2_secret_access_key'));
            $bucket = trim($request->r2_bucket ?? Setting::get('r2_bucket'));
            $endpoint = trim($request->r2_endpoint ?? Setting::get('r2_endpoint'));
            $accountId = trim($request->r2_account_id ?? Setting::get('r2_account_id'));
            $url = trim($request->r2_url ?? Setting::get('r2_url'));

            if (empty($endpoint) && !empty($accountId)) {
                $endpoint = "https://{$accountId}.r2.cloudflarestorage.com";
            }

            if (empty($accessKey) || empty($secretKey) || empty($bucket)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kredensial R2 belum lengkap. Mohon isi Access Key ID, Secret Access Key, dan Bucket Name.'
                ], 422);
            }

            config([
                'filesystems.disks.r2_test' => [
                    'driver' => 's3',
                    'key' => $accessKey,
                    'secret' => $secretKey,
                    'region' => 'auto',
                    'bucket' => $bucket,
                    'url' => $url,
                    'endpoint' => $endpoint,
                    'use_path_style_endpoint' => true,
                    'throw' => true,
                ]
            ]);

            $testFileName = 'r2_test_connection_' . time() . '.txt';
            $testContent = 'Pengentani R2 Connection Test - ' . date('Y-m-d H:i:s');

            Storage::disk('r2_test')->put($testFileName, $testContent);
            $readBack = Storage::disk('r2_test')->get($testFileName);
            Storage::disk('r2_test')->delete($testFileName);

            if ($readBack === $testContent) {
                return response()->json([
                    'success' => true,
                    'message' => "Koneksi Cloudflare R2 Berhasil! Berkas tes berhasil diunggah dan dibaca dari bucket '$bucket'."
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi isi berkas tes dari Cloudflare R2.'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Koneksi ke Cloudflare R2 Gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function migrateLocalToR2(Request $request)
    {
        try {
            $localPath = storage_path('app/public');
            if (!File::isDirectory($localPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Direktori penyimpanan lokal (storage/app/public) tidak ditemukan.'
                ], 404);
            }

            $allFiles = File::allFiles($localPath);
            $migratedCount = 0;
            $failedCount = 0;
            $totalBytes = 0;
            $errors = [];

            $this->applyR2Config();

            foreach ($allFiles as $file) {
                $filename = $file->getFilename();
                if ($filename === '.gitignore') {
                    continue;
                }

                $relativePath = str_replace('\\', '/', $file->getRelativePathname());
                $fileBytes = $file->getSize();

                try {
                    $stream = fopen($file->getRealPath(), 'r+');
                    $success = Storage::disk('r2')->put($relativePath, $stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }

                    if ($success) {
                        $migratedCount++;
                        $totalBytes += $fileBytes;
                    } else {
                        $failedCount++;
                        $errors[] = "Gagal upload: {$relativePath}";
                    }
                } catch (\Exception $ex) {
                    $failedCount++;
                    $errors[] = "Error ({$relativePath}): " . $ex->getMessage();
                }
            }

            $formattedBytes = $this->formatBytes($totalBytes);

            return response()->json([
                'success' => true,
                'message' => "Migrasi selesai! {$migratedCount} file ({$formattedBytes}) berhasil diunggah ke Cloudflare R2." . ($failedCount > 0 ? " ({$failedCount} file gagal)" : ""),
                'migrated_count' => $migratedCount,
                'failed_count' => $failedCount,
                'total_bytes_formatted' => $formattedBytes,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat migrasi: ' . $e->getMessage()
            ], 500);
        }
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
