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

            // Execute git pull
            $output = shell_exec('git pull 2>&1');
            if ($output === null) {
                $output = 'Gagal menjalankan perintah shell_exec git pull.';
            }

            Log::info("Git pull executed by user ID {$user->id} ({$user->name}): " . $output);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Git pull berhasil dieksekusi',
                    'output' => trim($output)
                ]);
            }

            return view('git_pull_result', [
                'output' => trim($output),
                'user' => $user,
                'isError' => false
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
