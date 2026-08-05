<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GitPullController extends Controller
{
    public function handle(Request $request)
    {
        $user = Auth::user();

        // Strict authorization check for Super Admin
        if (!$user || !method_exists($user, 'isSuperAdmin') || !$user->isSuperAdmin()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya Super Admin yang diizinkan melakukan git pull.'
                ], 403);
            }
            
            abort(403, 'Akses ditolak. Hanya Super Admin yang diizinkan melakukan git pull.');
        }

        try {
            $basePath = base_path();
            chdir($basePath);

            // Automatically configure safe directory both globally and inline to fix dubious ownership on Linux servers
            shell_exec('git config --global --add safe.directory ' . escapeshellarg($basePath) . ' 2>&1');
            shell_exec('git config --global --add safe.directory "*" 2>&1');

            // Execute git pull with inline safe.directory configuration
            $output = shell_exec('git -c safe.directory="*" -c safe.directory=' . escapeshellarg($basePath) . ' pull 2>&1');
            if ($output === null) {
                $output = 'Gagal menjalankan perintah shell_exec git pull.';
            }

            $trimmedOutput = trim($output);
            $isError = (bool) preg_match('/(fatal:|error:|Permission denied)/i', $trimmedOutput);

            Log::info("Git pull executed by user ID {$user->id} ({$user->name}): " . $trimmedOutput);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => !$isError,
                    'message' => $isError ? 'Gagal mengeksekusi git pull' : 'Git pull berhasil dieksekusi',
                    'output' => $trimmedOutput
                ], $isError ? 500 : 200);
            }

            return view('git_pull_result', [
                'output' => $trimmedOutput,
                'user' => $user,
                'isError' => $isError
            ]);
        } catch (\Exception $e) {
            Log::error("Git pull error: " . $e->getMessage());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengeksekusi git pull: ' . $e->getMessage()
                ], 500);
            }

            return view('git_pull_result', [
                'output' => 'Error: ' . $e->getMessage(),
                'user' => $user,
                'isError' => true
            ]);
        }
    }
}
