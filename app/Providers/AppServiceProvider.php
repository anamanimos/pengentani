<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (Schema::hasTable('settings')) {
                $driver = Setting::get('storage_driver', env('FILESYSTEM_DISK', 'public'));
                $accountId = Setting::get('r2_account_id', env('R2_ACCOUNT_ID'));
                $accessKey = Setting::get('r2_access_key_id', env('R2_ACCESS_KEY_ID'));
                $secretKey = Setting::get('r2_secret_access_key', env('R2_SECRET_ACCESS_KEY'));
                $bucket = Setting::get('r2_bucket', env('R2_BUCKET'));
                $url = Setting::get('r2_url', env('R2_URL'));
                $endpoint = Setting::get('r2_endpoint', env('R2_ENDPOINT'));

                if (empty($endpoint) && !empty($accountId)) {
                    $endpoint = "https://{$accountId}.r2.cloudflarestorage.com";
                }

                if (!empty($accessKey) && !empty($secretKey) && !empty($bucket)) {
                    config([
                        'filesystems.disks.r2.key' => $accessKey,
                        'filesystems.disks.r2.secret' => $secretKey,
                        'filesystems.disks.r2.bucket' => $bucket,
                        'filesystems.disks.r2.url' => !empty($url) ? rtrim($url, '/') : null,
                        'filesystems.disks.r2.endpoint' => $endpoint,
                        'filesystems.disks.r2.region' => 'auto',
                        'filesystems.disks.r2.use_path_style_endpoint' => true,
                    ]);
                }

                if ($driver === 'r2') {
                    config(['filesystems.default' => 'r2']);
                    config(['filesystems.disks.public' => config('filesystems.disks.r2')]);
                }
            }
        } catch (\Throwable $e) {
            // Prevent boot failure during initial migrations or setup
        }
    }
}
