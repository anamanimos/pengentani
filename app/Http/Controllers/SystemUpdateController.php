<?php

namespace App\Http\Controllers;

use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

class SystemUpdateController extends Controller
{
    /**
     * Check if current user is authorized to perform system operations.
     */
    protected function checkAuthorization(): void
    {
        if (!auth()->check() || (!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())) {
            abort(403, 'Akses terbatas untuk Administrator.');
        }
    }

    /**
     * Display System Update & Migration dashboard.
     */
    public function index()
    {
        $this->checkAuthorization();

        $gitBranch = 'unknown';
        $gitCommit = 'unknown';
        $gitAuthor = '';
        $gitDate = '';
        $gitMessage = '';
        $gitClean = true;
        $gitRemoteUrl = '';

        try {
            $branchProcess = Process::path(base_path())->run('git -c safe.directory=* rev-parse --abbrev-ref HEAD');
            if ($branchProcess->successful()) {
                $gitBranch = trim($branchProcess->output());
            }

            $logProcess = Process::path(base_path())->run('git -c safe.directory=* log -1 --format="%h|%an|%ar|%s"');
            if ($logProcess->successful() && !empty(trim($logProcess->output()))) {
                $parts = explode('|', trim($logProcess->output()), 4);
                $gitCommit = $parts[0] ?? '';
                $gitAuthor = $parts[1] ?? '';
                $gitDate = $parts[2] ?? '';
                $gitMessage = $parts[3] ?? '';
            }

            $statusProcess = Process::path(base_path())->run('git -c safe.directory=* status --porcelain');
            if ($statusProcess->successful()) {
                $gitClean = empty(trim($statusProcess->output()));
            }

            $remoteProcess = Process::path(base_path())->run('git -c safe.directory=* remote get-url origin');
            if ($remoteProcess->successful()) {
                $rawUrl = trim($remoteProcess->output());
                // Mask credentials for security
                $gitRemoteUrl = preg_replace('/https:\/\/[^@]+@/', 'https://', $rawUrl);
            }
        } catch (\Throwable $e) {
            // Log or ignore if git is not available
        }

        // Migration status
        $pendingMigrations = [];
        try {
            $migrator = app('migrator');
            $files = $migrator->getMigrationFiles(database_path('migrations'));
            $ran = $migrator->getRepository()->getRan();
            $pendingMigrations = array_values(array_diff(array_keys($files), $ran));
        } catch (\Throwable $e) {
            // Ignore
        }

        $phpVersion = PHP_VERSION;
        $laravelVersion = app()->version();
        $environment = app()->environment();
        $isProduction = app()->isProduction();

        return view('settings.system', compact(
            'gitBranch',
            'gitCommit',
            'gitAuthor',
            'gitDate',
            'gitMessage',
            'gitClean',
            'gitRemoteUrl',
            'pendingMigrations',
            'phpVersion',
            'laravelVersion',
            'environment',
            'isProduction'
        ));
    }

    /**
     * Run git pull from remote repository.
     */
    public function pull(Request $request)
    {
        $this->checkAuthorization();

        $branch = $request->input('branch');
        if (empty($branch) || !preg_match('/^[a-zA-Z0-9_\-\.\/]+$/', $branch)) {
            $branch = 'main';
            try {
                $curBranch = trim(Process::path(base_path())->run('git -c safe.directory=* rev-parse --abbrev-ref HEAD')->output());
                if (!empty($curBranch) && preg_match('/^[a-zA-Z0-9_\-\.\/]+$/', $curBranch)) {
                    $branch = $curBranch;
                }
            } catch (\Throwable $e) {
                // Keep default 'main'
            }
        }

        $output = '';
        $exitCode = 0;
        $success = false;

        try {
            $result = Process::path(base_path())
                ->timeout(180)
                ->env([
                    'GIT_TERMINAL_PROMPT' => '0',
                ])
                ->run("git -c safe.directory=* pull origin {$branch}");

            $output = $result->output();
            if ($result->errorOutput()) {
                $output .= ($output ? "\n" : "") . $result->errorOutput();
            }
            $exitCode = $result->exitCode();
            $success = $result->successful();
        } catch (\Throwable $e) {
            $exitCode = 1;
            $output = 'Terjadi kesalahan saat mengeksekusi git pull: ' . $e->getMessage();
        }

        try {
            LogService::record('system', 'git_pull', 'Eksekusi git pull dari web console', [
                'branch' => $branch,
                'exit_code' => $exitCode,
                'output' => substr($output, 0, 1000)
            ], auth()->id());
        } catch (\Throwable $e) {
            // Ignore log error
        }

        return response()->json([
            'success' => $success,
            'exit_code' => $exitCode,
            'message' => $success ? 'Git pull berhasil dieksekusi!' : 'Git pull mengembalikan kode non-nol atau terjadi kendala.',
            'output' => $output ?: '(Tidak ada output)',
            'timestamp' => now()->translatedFormat('d M Y H:i:s'),
        ]);
    }

    /**
     * Run php artisan migrate.
     */
    public function migrate(Request $request)
    {
        $this->checkAuthorization();

        try {
            $exitCode = Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            $success = ($exitCode === 0);
        } catch (\Throwable $e) {
            $exitCode = 1;
            $output = 'Kesalahan saat menjalankan migrasi: ' . $e->getMessage();
            $success = false;
        }

        try {
            LogService::record('system', 'migrate', 'Eksekusi migrate database dari web console', [
                'exit_code' => $exitCode,
                'output' => substr($output, 0, 1000)
            ], auth()->id());
        } catch (\Throwable $e) {
            // Ignore log error
        }

        return response()->json([
            'success' => $success,
            'exit_code' => $exitCode,
            'message' => $success ? 'Migrasi database berhasil diselesaikan!' : 'Migrasi database mengalami kendala.',
            'output' => $output ?: '(Tidak ada output)',
            'timestamp' => now()->translatedFormat('d M Y H:i:s'),
        ]);
    }

    /**
     * Run php artisan migrate:status.
     */
    public function migrateStatus(Request $request)
    {
        $this->checkAuthorization();

        try {
            $exitCode = Artisan::call('migrate:status');
            $output = Artisan::output();
            $success = true;
        } catch (\Throwable $e) {
            $exitCode = 1;
            $output = 'Kesalahan saat memeriksa status migrasi: ' . $e->getMessage();
            $success = false;
        }

        return response()->json([
            'success' => $success,
            'exit_code' => $exitCode,
            'output' => $output ?: '(Tidak ada output)',
            'timestamp' => now()->translatedFormat('d M Y H:i:s'),
        ]);
    }

    /**
     * Clear application cache (optimize:clear).
     */
    public function clearCache(Request $request)
    {
        $this->checkAuthorization();

        try {
            $exitCode = Artisan::call('optimize:clear');
            $output = Artisan::output();
            $success = ($exitCode === 0);
        } catch (\Throwable $e) {
            $exitCode = 1;
            $output = 'Kesalahan saat membersihkan cache: ' . $e->getMessage();
            $success = false;
        }

        try {
            LogService::record('system', 'clear_cache', 'Eksekusi optimize:clear dari web console', [
                'exit_code' => $exitCode,
                'output' => substr($output, 0, 1000)
            ], auth()->id());
        } catch (\Throwable $e) {
            // Ignore log error
        }

        return response()->json([
            'success' => $success,
            'exit_code' => $exitCode,
            'message' => $success ? 'Cache sistem berhasil dibersihkan!' : 'Pembersihan cache mengalami kendala.',
            'output' => $output ?: '(Tidak ada output)',
            'timestamp' => now()->translatedFormat('d M Y H:i:s'),
        ]);
    }

    /**
     * Get detailed git status and recent commit history.
     */
    public function gitStatus(Request $request)
    {
        $this->checkAuthorization();

        try {
            $statusResult = Process::path(base_path())->run('git -c safe.directory=* status');
            $logResult = Process::path(base_path())->run('git -c safe.directory=* log -5 --oneline');

            $output = "=== STATUS WORKING TREE ===\n" . $statusResult->output() . "\n=== 5 COMMIT TERAKHIR ===\n" . $logResult->output();
            $success = true;
            $exitCode = 0;
        } catch (\Throwable $e) {
            $output = 'Kesalahan saat memeriksa status Git: ' . $e->getMessage();
            $success = false;
            $exitCode = 1;
        }

        return response()->json([
            'success' => $success,
            'exit_code' => $exitCode,
            'output' => $output,
            'timestamp' => now()->translatedFormat('d M Y H:i:s'),
        ]);
    }

    /**
     * Fetch from remote and check how many commits behind.
     */
    public function checkRemote(Request $request)
    {
        $this->checkAuthorization();

        $behindCount = 0;
        $output = '';
        $success = false;

        try {
            $fetchResult = Process::path(base_path())
                ->timeout(60)
                ->env(['GIT_TERMINAL_PROMPT' => '0'])
                ->run('git -c safe.directory=* fetch origin');

            $curBranch = 'main';
            $branchProc = Process::path(base_path())->run('git -c safe.directory=* rev-parse --abbrev-ref HEAD');
            if ($branchProc->successful()) {
                $curBranch = trim($branchProc->output());
            }

            $diffProc = Process::path(base_path())->run("git -c safe.directory=* log HEAD..origin/{$curBranch} --oneline");
            $diffOutput = trim($diffProc->output());

            if (!empty($diffOutput)) {
                $commits = array_filter(explode("\n", $diffOutput));
                $behindCount = count($commits);
                $output = "Ditemukan {$behindCount} commit baru di remote (origin/{$curBranch}):\n\n" . $diffOutput;
            } else {
                $output = "Kode lokal Anda sudah mutakhir dengan repository remote (origin/{$curBranch}).";
            }
            $success = true;
        } catch (\Throwable $e) {
            $output = 'Gagal melakukan fetch: ' . $e->getMessage();
        }

        return response()->json([
            'success' => $success,
            'behind_count' => $behindCount,
            'output' => $output,
            'timestamp' => now()->translatedFormat('d M Y H:i:s'),
        ]);
    }
}
