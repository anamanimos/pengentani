<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,pengelola,investor,pekerja',
        ]);

        $user = User::create([
            'name' => $request->name,
            'whatsapp' => $request->whatsapp,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        if ($request->has('send_wa') && $user->whatsapp) {
            $this->sendInvitation($user);
        }

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Pengguna berhasil ditambahkan.',
                'redirect' => route('users.index')
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function sendInvitation(User $user)
    {
        if (!$user->whatsapp) {
            return redirect()->back()->with('error', 'Pengguna ini tidak memiliki nomor WhatsApp.');
        }

        $message = "Halo {$user->name}!\n\nAkun Anda telah didaftarkan di sistem PengenTani. Anda dapat masuk ke sistem dengan mudah, cukup balas pesan ini dengan mengetik kata *login*.\n\nKami akan membalas dengan tautan ajaib untuk masuk otomatis tanpa password.";
        
        $success = \App\Services\WaGatewayService::sendMessage($user->whatsapp, $message);

        if (request()->ajax()) {
            return response()->json([
                'message' => $success ? 'Informasi pendaftaran berhasil dikirim via WhatsApp.' : 'Gagal mengirim WhatsApp.',
            ], $success ? 200 : 500);
        }

        if ($success) {
            return redirect()->back()->with('success', 'Informasi pendaftaran berhasil dikirim via WhatsApp.');
        }

        return redirect()->back()->with('error', 'Gagal mengirim informasi pendaftaran via WhatsApp.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|string|in:admin,pengelola,investor,pekerja',
        ]);

        $data = [
            'name' => $request->name,
            'whatsapp' => $request->whatsapp,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Pengguna berhasil diperbarui.',
                'redirect' => route('users.index')
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    public function impersonate(User $user)
    {
        // Pastikan hanya admin yang bisa melakukan impersonate
        if (!Auth::user()->isAdmin() && !Auth::user()->isPengelola()) {
            return redirect()->back()->with('error', 'Tidak memiliki izin akses.');
        }

        // Jangan impersonate diri sendiri
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Tidak bisa login sebagai diri sendiri.');
        }

        session()->put('impersonate_by', Auth::id());
        Auth::login($user);

        // Arahkan ke dashboard yang sesuai
        if ($user->isInvestor()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('console.dashboard');
    }

    public function stopImpersonate()
    {
        if (session()->has('impersonate_by')) {
            $original_id = session()->get('impersonate_by');
            session()->forget('impersonate_by');
            
            Auth::loginUsingId($original_id);
            
            return redirect()->route('users.index')->with('success', 'Berhasil kembali ke akun Admin Anda.');
        }

        return redirect()->back();
    }

    public function toggleActive(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()->with('success', "Akun pengguna berhasil $status.");
    }
}
